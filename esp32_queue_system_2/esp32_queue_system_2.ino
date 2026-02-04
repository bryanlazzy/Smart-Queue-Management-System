
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// ==================== CONFIGURATION ====================
// WiFi Credentials
const char* ssid = "DLSU-D ADG";
const char* password = "Ayunt@mient0";

// Your Website URL
const char* serverUrl = "https://sqms.online";

// API Endpoints
String getPendingSmsUrl = String(serverUrl) + "/get_pending_sms.php";
String markSmsSentUrl = String(serverUrl) + "/mark_sms_sent.php";

// SIM800L Serial Communication (v2 EVB uses 115200)
#define SIM800_RX 16
#define SIM800_TX 17
HardwareSerial sim800(2);

// Timing Configuration
const unsigned long CHECK_INTERVAL = 5000;      // Check every 5 seconds
const unsigned long SMS_DELAY = 3000;           // Delay between SMS sends
unsigned long lastCheck = 0;

// Memory Management Configuration
#define MAX_TRACKED_QUEUES 600              // Maximum queues to track (circular buffer)
#define BATCH_SIZE 10                       // Process 10 SMS at a time
#define MIN_FREE_HEAP 50000                 // Minimum free heap before cleanup (bytes)
#define MEMORY_CHECK_INTERVAL 30000         // Check memory every 30 seconds
unsigned long lastMemoryCheck = 0;

// Status tracking
bool systemReady = false;
int totalSmsSent = 0;

// ==================== MEMORY-EFFICIENT TRACKING ====================
// Circular buffer for tracking sent queues
struct SentQueueEntry {
  char serviceQueue[64];  // "ServiceName-QueueNumber" (max 63 chars + null terminator)
  unsigned long timestamp;
  bool active;
};

SentQueueEntry sentQueuesBuffer[MAX_TRACKED_QUEUES];
int bufferHead = 0;  // Next position to write
int activeEntries = 0;

// Hash function for quick lookup (simple but effective)
unsigned long hashString(const char* str) {
  unsigned long hash = 5381;
  int c;
  while ((c = *str++)) {
    hash = ((hash << 5) + hash) + c; // hash * 33 + c
  }
  return hash;
}

// ==================== SETUP ====================
void setup() {
  Serial.begin(115200);
  sim800.begin(115200, SERIAL_8N1, SIM800_RX, SIM800_TX);
  
  delay(3000);
  printHeader();
  
  // Initialize tracking buffer
  initializeTrackingBuffer();
  
  // Print memory info
  printMemoryInfo();
  
  // Initialize WiFi
  if (!connectWiFi()) {
    Serial.println("CRITICAL: WiFi connection failed!");
    Serial.println("System cannot continue without internet.");
    while(1) { delay(1000); }
  }
  
  // Initialize SIM800L
  if (!initSIM800L()) {
    Serial.println("WARNING: SIM800L initialization had issues.");
    Serial.println("Check connections and SIM card.");
  }
  
  testServerConnection();
  
  systemReady = true;
  Serial.println("\n╔═══════════════════════════════════════════╗");
  Serial.println("║   🟢 SYSTEM READY - MONITORING ACTIVE   ║");
  Serial.println("╚═══════════════════════════════════════════╝\n");
  
  printMemoryInfo();
}

// ==================== MAIN LOOP ====================
void loop() {
  // WiFi connection check
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\n⚠️  WiFi disconnected! Attempting reconnection...");
    connectWiFi();
  }
  
  // Periodic memory check and cleanup
  if (millis() - lastMemoryCheck >= MEMORY_CHECK_INTERVAL) {
    lastMemoryCheck = millis();
    performMemoryMaintenance();
  }
  
  // Check for pending SMS
  if (millis() - lastCheck >= CHECK_INTERVAL) {
    lastCheck = millis();
    checkAndSendPendingSMS();
  }
  
  // Handle SIM800L responses
  while (sim800.available()) {
    Serial.write(sim800.read());
  }
  
  delay(100);
}

// ==================== MEMORY MANAGEMENT ====================
void initializeTrackingBuffer() {
  Serial.println("🔧 Initializing tracking buffer...");
  for (int i = 0; i < MAX_TRACKED_QUEUES; i++) {
    sentQueuesBuffer[i].serviceQueue[0] = '\0';
    sentQueuesBuffer[i].timestamp = 0;
    sentQueuesBuffer[i].active = false;
  }
  bufferHead = 0;
  activeEntries = 0;
  Serial.println("   ✅ Buffer initialized (capacity: " + String(MAX_TRACKED_QUEUES) + " entries)");
}

void printMemoryInfo() {
  Serial.println("\n📊 MEMORY STATUS:");
  Serial.println("   Free Heap: " + String(ESP.getFreeHeap()) + " bytes");
  Serial.println("   Heap Size: " + String(ESP.getHeapSize()) + " bytes");
  Serial.println("   Active Tracked Queues: " + String(activeEntries) + "/" + String(MAX_TRACKED_QUEUES));
  Serial.println("   Buffer Usage: " + String((activeEntries * 100) / MAX_TRACKED_QUEUES) + "%");
}

void performMemoryMaintenance() {
  Serial.println("\n🔧 Performing memory maintenance...");
  
  uint32_t freeHeap = ESP.getFreeHeap();
  Serial.println("   Current Free Heap: " + String(freeHeap) + " bytes");
  
  if (freeHeap < MIN_FREE_HEAP) {
    Serial.println("   ⚠️  Low memory detected! Performing cleanup...");
    cleanupOldEntries(300);  // Remove entries older than 5 minutes
    
    // If still low, remove older entries
    if (ESP.getFreeHeap() < MIN_FREE_HEAP) {
      Serial.println("   ⚠️  Still low! Aggressive cleanup...");
      cleanupOldEntries(120);  // Remove entries older than 2 minutes
    }
  }
  
  printMemoryInfo();
}

void cleanupOldEntries(int maxAgeSeconds) {
  unsigned long currentTime = millis();
  int removed = 0;
  
  for (int i = 0; i < MAX_TRACKED_QUEUES; i++) {
    if (sentQueuesBuffer[i].active) {
      unsigned long age = (currentTime - sentQueuesBuffer[i].timestamp) / 1000;
      if (age > maxAgeSeconds) {
        sentQueuesBuffer[i].active = false;
        sentQueuesBuffer[i].serviceQueue[0] = '\0';
        activeEntries--;
        removed++;
      }
    }
  }
  
  Serial.println("   🗑️  Removed " + String(removed) + " old entries (>" + String(maxAgeSeconds) + "s)");
}

bool isQueueTracked(const char* serviceQueue) {
  // Linear search with hash optimization
  unsigned long hash = hashString(serviceQueue);
  
  for (int i = 0; i < MAX_TRACKED_QUEUES; i++) {
    if (sentQueuesBuffer[i].active) {
      if (strcmp(sentQueuesBuffer[i].serviceQueue, serviceQueue) == 0) {
        return true;
      }
    }
  }
  return false;
}

void addToTracking(const char* serviceQueue) {
  // Check if already tracked
  if (isQueueTracked(serviceQueue)) {
    Serial.println("   ℹ️  Already tracked: " + String(serviceQueue));
    return;
  }
  
  // Find next available slot (circular buffer)
  int attempts = 0;
  while (attempts < MAX_TRACKED_QUEUES) {
    if (!sentQueuesBuffer[bufferHead].active) {
      // Found empty slot
      break;
    }
    bufferHead = (bufferHead + 1) % MAX_TRACKED_QUEUES;
    attempts++;
  }
  
  // If buffer is full, overwrite oldest entry
  if (attempts >= MAX_TRACKED_QUEUES) {
    Serial.println("   ⚠️  Buffer full! Overwriting oldest entry");
    if (sentQueuesBuffer[bufferHead].active) {
      activeEntries--;  // We're replacing an active entry
    }
  }
  
  // Add new entry
  strncpy(sentQueuesBuffer[bufferHead].serviceQueue, serviceQueue, 63);
  sentQueuesBuffer[bufferHead].serviceQueue[63] = '\0';
  sentQueuesBuffer[bufferHead].timestamp = millis();
  sentQueuesBuffer[bufferHead].active = true;
  activeEntries++;
  
  // Move head forward
  bufferHead = (bufferHead + 1) % MAX_TRACKED_QUEUES;
}

void clearAllTracking() {
  Serial.println("   🗑️  Clearing all tracking...");
  for (int i = 0; i < MAX_TRACKED_QUEUES; i++) {
    sentQueuesBuffer[i].active = false;
    sentQueuesBuffer[i].serviceQueue[0] = '\0';
  }
  activeEntries = 0;
  bufferHead = 0;
}

// ==================== WIFI FUNCTIONS ====================
bool connectWiFi() {
  Serial.println("\n📡 Connecting to WiFi: " + String(ssid));
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 30) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi Connected!");
    Serial.print("   IP Address: ");
    Serial.println(WiFi.localIP());
    Serial.print("   Signal Strength: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
    return true;
  }
  
  Serial.println("\n❌ WiFi Connection Failed!");
  return false;
}

// ==================== SIM800L FUNCTIONS ====================
bool initSIM800L() {
  Serial.println("\n📱 Initializing SIM800L v2 EVB Module...");
  delay(2000);
  
  bool success = true;
  
  Serial.println("   Testing module response...");
  if (!sendATCommand("AT", 2000, "OK")) {
    Serial.println("   ❌ Module not responding");
    success = false;
  } else {
    Serial.println("   ✅ Module responding");
  }
  
  sendATCommand("ATE0", 1000, "OK");
  
  Serial.println("   Setting SMS text mode...");
  if (sendATCommand("AT+CMGF=1", 1000, "OK")) {
    Serial.println("   ✅ SMS mode set");
  }
  
  sendATCommand("AT+CSCS=\"GSM\"", 1000, "OK");
  
  Serial.println("   Checking signal strength...");
  sendATCommand("AT+CSQ", 2000, "+CSQ");
  
  Serial.println("   Checking SIM card status...");
  if (sendATCommand("AT+CPIN?", 2000, "READY")) {
    Serial.println("   ✅ SIM card ready");
  } else {
    Serial.println("   ❌ SIM card not ready");
    success = false;
  }
  
  Serial.println("   Checking network registration...");
  sendATCommand("AT+CREG?", 2000, "+CREG");
  
  if (success) {
    Serial.println("✅ SIM800L initialized successfully!\n");
  } else {
    Serial.println("⚠️  SIM800L initialization completed with warnings\n");
  }
  
  return success;
}

bool sendATCommand(String command, int timeout, String expectedResponse) {
  Serial.print("   AT: ");
  Serial.println(command);
  
  sim800.println(command);
  
  long startTime = millis();
  String response = "";
  bool found = false;
  
  while (millis() - startTime < timeout) {
    while (sim800.available()) {
      char c = sim800.read();
      response += c;
      Serial.write(c);
      
      if (response.indexOf(expectedResponse) != -1) {
        found = true;
      }
    }
    if (found) break;
  }
  
  Serial.println();
  return found;
}

// ==================== SERVER COMMUNICATION ====================
void testServerConnection() {
  Serial.println("\n🌐 Testing server connection...");
  Serial.println("   URL: " + getPendingSmsUrl);
  
  HTTPClient http;
  http.begin(getPendingSmsUrl);
  http.setTimeout(10000);
  
  int httpCode = http.GET();
  
  if (httpCode > 0) {
    Serial.print("   HTTP Response Code: ");
    Serial.println(httpCode);
    
    if (httpCode == HTTP_CODE_OK) {
      String payload = http.getString();
      Serial.println("   ✅ Server connection successful!");
      Serial.println("   Response preview: " + payload.substring(0, min(100, (int)payload.length())));
    } else {
      Serial.println("   ⚠️  Unexpected response code");
    }
  } else {
    Serial.print("   ❌ Connection failed: ");
    Serial.println(http.errorToString(httpCode));
  }
  
  http.end();
}

void checkAndSendPendingSMS() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠️  No WiFi - skipping check");
    return;
  }
  
  Serial.println("\n🔍 Checking for pending SMS...");
  Serial.println("   Free Heap: " + String(ESP.getFreeHeap()) + " bytes");
  
  HTTPClient http;
  http.begin(getPendingSmsUrl);
  http.setTimeout(15000);  // Increased timeout for large responses
  
  int httpCode = http.GET();
  
  if (httpCode == HTTP_CODE_OK) {
    String payload = http.getString();
    
    // Use larger JSON buffer for 500+ queues
    // Each queue entry ~150 bytes, 500 queues = 75KB + overhead
    DynamicJsonDocument doc(100000);  // 100KB buffer
    
    DeserializationError error = deserializeJson(doc, payload);
    
    if (error) {
      Serial.print("❌ JSON parsing failed: ");
      Serial.println(error.c_str());
      
      // Try to free memory and retry with smaller buffer
      Serial.println("   Attempting recovery with smaller buffer...");
      doc.clear();
      doc.shrinkToFit();
      
      http.end();
      return;
    }
    
    bool success = doc["success"];
    int pendingCount = doc["count"];
    
    Serial.print("   Pending SMS: ");
    Serial.println(pendingCount);
    
    if (success && pendingCount > 0) {
      JsonArray pending = doc["pending"].as<JsonArray>();
      
      // BATCH PROCESSING for memory efficiency
      int processed = 0;
      int sent = 0;
      
      Serial.println("\n Processing in batches of " + String(BATCH_SIZE));
      
      for (JsonObject entry : pending) {
        // Check memory before processing each batch
        if (processed % BATCH_SIZE == 0 && processed > 0) {
          Serial.println("\n   📊 Batch checkpoint:");
          Serial.println("      Processed: " + String(processed) + "/" + String(pendingCount));
          Serial.println("      Free Heap: " + String(ESP.getFreeHeap()) + " bytes");
          
          // If memory is low, cleanup
          if (ESP.getFreeHeap() < MIN_FREE_HEAP) {
            Serial.println("      ⚠️  Low memory! Cleaning up...");
            cleanupOldEntries(180);
          }
          
          delay(1000);  // Brief pause between batches
        }
        
        int queueNumber = entry["queuenumber"];
        String name = entry["name"].as<String>();
        String contactNumber = entry["contactnumber"].as<String>();
        String service = entry["service"].as<String>();
        String notificationType = entry["notification_type"].as<String>();
        String table = entry["table"].as<String>();
        
        // Create unique key: "Service Name-QueueNumber"
        String serviceQueueKey = service + "-" + String(queueNumber);
        
        // Convert to char array for tracking
        char serviceQueueChar[64];
        serviceQueueKey.toCharArray(serviceQueueChar, 64);
        
        // Check if already tracked
        if (isQueueTracked(serviceQueueChar)) {
          Serial.println("   Skipping " + serviceQueueKey + " - Already sent");
          processed++;
          continue;
        }
        
        // ONLY send if notification_type is "registered"
        if (notificationType == "registered") {
          if (sendQueueSMS(queueNumber, name, contactNumber, service)) {
            // Add to tracking
            addToTracking(serviceQueueChar);
            
            // Mark as sent in database
            markAsSent(queueNumber, "registered", table);
            
            totalSmsSent++;
            sent++;
            
            Serial.println("   ✅ Sent SMS for " + serviceQueueKey);
            Serial.println("   📊 Session: " + String(sent) + " sent, Total: " + String(totalSmsSent));
            Serial.println("   📋 Tracked: " + String(activeEntries) + "/" + String(MAX_TRACKED_QUEUES));
            
            delay(SMS_DELAY);
          } else {
            Serial.println("   ❌ Failed to send SMS for " + serviceQueueKey);
          }
        }
        
        processed++;
      }
      
      Serial.println("\n✅ Batch processing complete:");
      Serial.println("   Total processed: " + String(processed));
      Serial.println("   SMS sent this cycle: " + String(sent));
      
    } else {
      Serial.println("   ℹ️  No pending SMS");
      
      // If no queues at all, clear all tracking
      if (pendingCount == 0 && activeEntries > 0) {
        Serial.println("   🗑️  No queues in system - clearing tracking");
        clearAllTracking();
      }
    }
    
    // Clean up JSON document
    doc.clear();
    doc.shrinkToFit();
    
  } else {
    Serial.print("   ❌ HTTP Error: ");
    Serial.println(httpCode);
  }
  
  http.end();
  
  // Final memory status
  Serial.println("   Final Free Heap: " + String(ESP.getFreeHeap()) + " bytes");
}

bool sendQueueSMS(int queueNumber, String name, String contactNumber, String service) {
  String formattedNumber = formatPhoneNumber(contactNumber);
  
  // Optimized message - shorter to save memory and SMS costs
  String message = "SQMS - Queue Registered!\n\n";
  message += "Hi " + name + "!\n\n";
  message += "Your Queue #: " + String(queueNumber) + "\n";
  message += "Service: " + service + "\n\n";
  message += "Please wait for your turn.\n";
  message += "Check queue: SQMS . Online\n\n";
  message += "Thank you!";
  
  Serial.println("\n📤 SENDING SMS (registered)");
  Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  Serial.println("To: " + formattedNumber);
  Serial.println("Queue #: " + String(queueNumber));
  Serial.println("Service: " + service);
  Serial.println("Message Length: " + String(message.length()) + " chars");
  Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
  
  bool result = sendSMS(formattedNumber, message);
  
  // Clear strings to free memory
  message = "";
  formattedNumber = "";
  
  return result;
}

bool sendSMS(String phoneNumber, String message) {
  sim800.println("AT+CMGF=1");
  delay(1000);
  
  sim800.print("AT+CMGS=\"");
  sim800.print(phoneNumber);
  sim800.println("\"");
  delay(1000);
  
  String response = "";
  long timeout = millis() + 3000;
  while (millis() < timeout) {
    if (sim800.available()) {
      char c = sim800.read();
      response += c;
      Serial.write(c);
      if (response.indexOf(">") != -1) {
        break;
      }
    }
  }
  
  if (response.indexOf(">") == -1) {
    Serial.println("❌ No prompt received");
    return false;
  }
  
  sim800.print(message);
  delay(500);
  
  // Send Ctrl+Z, wait 100ms, then send newline
  sim800.write(26);
  delay(100);
  sim800.println();
  
  response = "";
  timeout = millis() + 15000;
  bool success = false;
  
  while (millis() < timeout) {
    while (sim800.available()) {
      char c = sim800.read();
      response += c;
      Serial.write(c);
      
      if (response.indexOf("OK") != -1 || response.indexOf("+CMGS") != -1) {
        success = true;
        break;
      }
      if (response.indexOf("ERROR") != -1){
        success = false;
      }
    }
    if (success || response.indexOf("ERROR") != -1) break;
  }
  
  if (success) {
    Serial.println("\n✅ SMS SENT SUCCESSFULLY!");
  } else {
    Serial.println("\n❌ SMS SEND FAILED!");
  }
  
  Serial.println("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n");
  
  return success;
}

void markAsSent(int queueNumber, String notificationType, String table) {
  HTTPClient http;
  http.begin(markSmsSentUrl);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  
  String postData = "queuenumber=" + String(queueNumber) + 
                    "&notification_type=" + notificationType +
                    "&table=" + table;
  
  int httpCode = http.POST(postData);
  
  if (httpCode == HTTP_CODE_OK) {
    Serial.println("   ✅ Database updated: Queue #" + String(queueNumber) + " (" + notificationType + ")");
  } else {
    Serial.println("   ⚠️  Failed to update database for queue #" + String(queueNumber));
    Serial.println("   ℹ️  Local tracking will still prevent duplicates");
  }
  
  http.end();
}

// ==================== HELPER FUNCTIONS ====================
String formatPhoneNumber(String number) {
  number.replace(" ", "");
  number.replace("-", "");
  
  if (number.startsWith("0")) {
    return "+63" + number.substring(1);
  }
  
  if (number.startsWith("63")) {
    return "+" + number;
  }
  
  if (!number.startsWith("+")) {
    return "+63" + number;
  }
  
  return number;
}

void printHeader() {
  Serial.println("\n╔════════════════════════════════════════════════════╗");
  Serial.println("║                                                    ║");
  Serial.println("║     SMART QUEUE MANAGEMENT SYSTEM (SQMS)           ║");
  Serial.println("║     ESP32 + SIM800L v2 EVB SMS Notifier            ║");
  Serial.println("║                                                    ║");
  Serial.println("║                                                    ║");
  Serial.println("║     Developed for:                                 ║");
  Serial.println("║     Ayuntamiento - De La Salle University          ║");
  Serial.println("║                                                    ║");
  Serial.println("╚════════════════════════════════════════════════════╝");
  Serial.println();
}
