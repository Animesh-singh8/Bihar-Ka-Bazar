"use client"

import { useState, useEffect } from "react"
import { View, Text, StyleSheet, ScrollView, Alert } from "react-native"
import { Card, Title, List, Switch, Divider } from "react-native-paper"
import AsyncStorage from "@react-native-async-storage/async-storage"

export default function SettingsScreen() {
  const [settings, setSettings] = useState({
    darkMode: false,
    notifications: true,
    autoSave: true,
    units: "metric", // metric or imperial
    language: "english",
  })

  useEffect(() => {
    loadSettings()
  }, [])

  const loadSettings = async () => {
    try {
      const savedSettings = await AsyncStorage.getItem("appSettings")
      if (savedSettings) {
        setSettings(JSON.parse(savedSettings))
      }
    } catch (error) {
      console.error("Error loading settings:", error)
    }
  }

  const saveSettings = async (newSettings) => {
    try {
      await AsyncStorage.setItem("appSettings", JSON.stringify(newSettings))
      setSettings(newSettings)
    } catch (error) {
      console.error("Error saving settings:", error)
    }
  }

  const handleSettingChange = (key, value) => {
    const newSettings = { ...settings, [key]: value }
    saveSettings(newSettings)
  }

  const clearAllData = () => {
    Alert.alert(
      "Clear All Data",
      "This will delete all your saved analyses and settings. This action cannot be undone.",
      [
        { text: "Cancel", style: "cancel" },
        {
          text: "Clear",
          style: "destructive",
          onPress: async () => {
            try {
              await AsyncStorage.clear()
              Alert.alert("Success", "All data has been cleared.")
              setSettings({
                darkMode: false,
                notifications: true,
                autoSave: true,
                units: "metric",
                language: "english",
              })
            } catch (error) {
              Alert.alert("Error", "Failed to clear data.")
            }
          },
        },
      ],
    )
  }

  const exportData = () => {
    Alert.alert("Export Data", "This feature will be available in a future update.", [{ text: "OK" }])
  }

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Title style={styles.headerTitle}>Settings</Title>
        <Text style={styles.headerSubtitle}>Customize your app experience</Text>
      </View>

      {/* Appearance Settings */}
      <Card style={styles.card}>
        <Card.Content>
          <Title style={styles.sectionTitle}>Appearance</Title>

          <List.Item
            title="Dark Mode"
            description="Use dark theme for better viewing in low light"
            left={(props) => <List.Icon {...props} icon="theme-light-dark" />}
            right={() => (
              <Switch value={settings.darkMode} onValueChange={(value) => handleSettingChange("darkMode", value)} />
            )}
          />
        </Card.Content>
      </Card>

      {/* Notification Settings */}
      <Card style={styles.card}>
        <Card.Content>
          <Title style={styles.sectionTitle}>Notifications</Title>

          <List.Item
            title="Push Notifications"
            description="Receive reminders and updates"
            left={(props) => <List.Icon {...props} icon="bell" />}
            right={() => (
              <Switch
                value={settings.notifications}
                onValueChange={(value) => handleSettingChange("notifications", value)}
              />
            )}
          />
        </Card.Content>
      </Card>

      {/* Data Settings */}
      <Card style={styles.card}>
        <Card.Content>
          <Title style={styles.sectionTitle}>Data & Storage</Title>

          <List.Item
            title="Auto-save Analyses"
            description="Automatically save your soil analyses"
            left={(props) => <List.Icon {...props} icon="content-save-auto" />}
            right={() => (
              <Switch value={settings.autoSave} onValueChange={(value) => handleSettingChange("autoSave", value)} />
            )}
          />

          <Divider style={styles.divider} />

          <List.Item
            title="Export Data"
            description="Export your analyses to external storage"
            left={(props) => <List.Icon {...props} icon="export" />}
            onPress={exportData}
          />

          <List.Item
            title="Clear All Data"
            description="Delete all saved analyses and reset settings"
            left={(props) => <List.Icon {...props} icon="delete-sweep" />}
            onPress={clearAllData}
            titleStyle={{ color: "#F44336" }}
          />
        </Card.Content>
      </Card>

      {/* Units Settings */}
      <Card style={styles.card}>
        <Card.Content>
          <Title style={styles.sectionTitle}>Units & Measurements</Title>

          <List.Item
            title="Measurement System"
            description={settings.units === "metric" ? "Metric (kg, hectares)" : "Imperial (lbs, acres)"}
            left={(props) => <List.Icon {...props} icon="ruler" />}
            onPress={() => {
              const newUnits = settings.units === "metric" ? "imperial" : "metric"
              handleSettingChange("units", newUnits)
            }}
          />
        </Card.Content>
      </Card>

      {/* Language Settings */}
      <Card style={styles.card}>
        <Card.Content>
          <Title style={styles.sectionTitle}>Language & Region</Title>

          <List.Item
            title="Language"
            description="English (More languages coming soon)"
            left={(props) => <List.Icon {...props} icon="translate" />}
            onPress={() => {
              Alert.alert("Language Settings", "Additional languages will be available in future updates.", [
                { text: "OK" },
              ])
            }}
          />
        </Card.Content>
      </Card>

      {/* About Section */}
      <Card style={styles.card}>
        <Card.Content>
          <Title style={styles.sectionTitle}>About</Title>

          <List.Item
            title="App Version"
            description="1.0.0"
            left={(props) => <List.Icon {...props} icon="information" />}
          />

          <List.Item
            title="Privacy Policy"
            description="View our privacy policy"
            left={(props) => <List.Icon {...props} icon="shield-account" />}
            onPress={() => {
              Alert.alert(
                "Privacy Policy",
                "Your data is stored locally on your device and is not shared with third parties without your consent.",
                [{ text: "OK" }],
              )
            }}
          />

          <List.Item
            title="Terms of Service"
            description="View terms and conditions"
            left={(props) => <List.Icon {...props} icon="file-document" />}
            onPress={() => {
              Alert.alert(
                "Terms of Service",
                "This app is provided for educational and informational purposes. Always consult with agricultural experts for professional advice.",
                [{ text: "OK" }],
              )
            }}
          />

          <List.Item
            title="Contact Support"
            description="Get help and support"
            left={(props) => <List.Icon {...props} icon="help-circle" />}
            onPress={() => {
              Alert.alert("Contact Support", "Email: support@soilanalyzer.com\nPhone: +1-234-567-8900", [
                { text: "OK" },
              ])
            }}
          />
        </Card.Content>
      </Card>

      <View style={styles.footer}>
        <Text style={styles.footerText}>SoilAnalyzer v1.0.0</Text>
        <Text style={styles.footerSubtext}>Made with ❤️ for farmers</Text>
      </View>
    </ScrollView>
  )
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#FAFAFA",
  },
  header: {
    padding: 20,
    backgroundColor: "#F5F5F5",
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: "bold",
    color: "#333",
  },
  headerSubtitle: {
    fontSize: 16,
    color: "#666",
    marginTop: 5,
  },
  card: {
    margin: 15,
    elevation: 2,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: "bold",
    marginBottom: 10,
    color: "#4CAF50",
  },
  divider: {
    marginVertical: 10,
  },
  footer: {
    padding: 30,
    alignItems: "center",
  },
  footerText: {
    fontSize: 16,
    fontWeight: "bold",
    color: "#666",
  },
  footerSubtext: {
    fontSize: 14,
    color: "#999",
    marginTop: 5,
  },
})
