/**
 * ESP32 Arduino Code - Bi-Directional Sync Example
 * Updated with "Sticky State" Logic
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// Pin Definitions
#define PUMP_PIN 25        // Pin untuk relay pompa
#define pH_SENSOR_PIN 34   // Pin sensor pH
#define LEVEL_SENSOR_PIN 35 // Pin sensor level air

// Server Configuration
const char* server_url = "http://your-server.com/input.php";
const char* wifi_ssid = "YOUR_WIFI_SSID";
const char* wifi_password = "YOUR_WIFI_PASSWORD";

// Device ID
const char* deviceId = "ESP32-001";

// Internal State Variable (Sticky Logic)
bool pumpStatus = false; 

void setup() {
  Serial.begin(115200);
  
  // Setup pins
  pinMode(PUMP_PIN, OUTPUT);
  digitalWrite(PUMP_PIN, LOW); // Pompa mati saat startup (Active High assumption)
  
  // Connect to WiFi
  WiFi.begin(wifi_ssid, wifi_password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Connected!");
}

void loop() {
  // Read sensors
  float phValue = readPH();
  float batteryLevel = readBattery();
  float waterLevel = readWaterLevel();
  int signalStrength = WiFi.RSSI();
  
  // LOGIKA STATE HOLDING (PENTING!)
  // Membaca status fisik ATAU status internal. 
  // Jika Active High: (digitalRead == HIGH) || pumpStatus
  // Jika Active Low: (digitalRead == LOW) || pumpStatus
  // Asumsi di sini Active HIGH (sesuai setup awal code ini).
  bool pumpIsOn = (digitalRead(PUMP_PIN) == HIGH) || pumpStatus;
  
  // Send data to server
  String command = sendDataToServer(phValue, batteryLevel, waterLevel, signalStrength, pumpIsOn);
  
  // Process command from server
  if (command == "ON") {
    digitalWrite(PUMP_PIN, HIGH); // Nyalakan (Active High)
    pumpStatus = true; // Kunci status internal jadi TRUE
    Serial.println("Pompa NYALA (from server)");
    
  } else if (command == "OFF") {
    digitalWrite(PUMP_PIN, LOW); // Matikan (Active High)
    pumpStatus = false; // Kunci status internal jadi FALSE
    Serial.println("Pompa MATI (from server)");
  }
  
  delay(5000); // Polling setiap 5 detik (lebih responsif dari 10s)
}

String sendDataToServer(float ph, float battery, float level, int signal, bool currentStatus) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi not connected!");
    return "ERROR";
  }
  
  HTTPClient http;
  http.begin(server_url);
  http.addHeader("Content-Type", "application/json");
  
  // Buat JSON payload
  DynamicJsonDocument doc(512);
  doc["ph"] = ph;
  doc["battery"] = battery;
  doc["level"] = level;
  doc["signal"] = signal;
  doc["deviceId"] = deviceId;
  doc["pump_status"] = currentStatus; // Kirim status gabungan (Fisik + Internal)
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  Serial.println("Sending: " + jsonString);
  
  int httpCode = http.POST(jsonString);
  String finalCommand = "ERROR";
  
  if (httpCode > 0) {
    String response = http.getString();
    Serial.println("Response: " + response);
    
    // Robust Parsing Logic
    response.toUpperCase(); 
    
    DynamicJsonDocument responseDoc(512);
    DeserializationError error = deserializeJson(responseDoc, response);
    
    if (!error) {
       String cmd = responseDoc["command"].as<String>();
       finalCommand = cmd;
    } else {
       // Fallback
       if (response.indexOf("\"COMMAND\":\"ON\"") >= 0 || response.indexOf("ON") >= 0) {
           if (response.indexOf("OFF") == -1) finalCommand = "ON";
       }
       if (response.indexOf("\"COMMAND\":\"OFF\"") >= 0 || response.indexOf("OFF") >= 0) {
           finalCommand = "OFF";
       }
    }
    
  } else {
    Serial.println("HTTP Error: " + String(httpCode));
  }
  
  http.end();
  return finalCommand;
}

// Helper functions (Dummy)
float readPH() { return 7.0; }
float readBattery() { return 85.0; }
float readWaterLevel() { return 50.0; }
