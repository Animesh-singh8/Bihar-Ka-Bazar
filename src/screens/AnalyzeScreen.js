"use client"

import { useState, useEffect } from "react"
import { View, Text, StyleSheet, ScrollView, Alert } from "react-native"
import { TextInput, Button, Card, Title, HelperText, Divider } from "react-native-paper"
import RNPickerSelect from "react-native-picker-select"
import { getStates, getDistricts, getCrops } from "../database/database"

export default function AnalyzeScreen({ navigation }) {
  const [formData, setFormData] = useState({
    nitrogen: "",
    phosphorus: "",
    potassium: "",
    ph: "",
    plotArea: "",
    areaUnit: "hectare",
    state: "",
    district: "",
    crop: "",
  })

  const [conversionData, setConversionData] = useState({
    nitrogenKgHa: 0,
    phosphorusKgHa: 0,
    potassiumKgHa: 0,
  })

  const [dropdownData, setDropdownData] = useState({
    states: [],
    districts: [],
    crops: [],
  })

  const [errors, setErrors] = useState({})

  useEffect(() => {
    loadInitialData()
  }, [])

  useEffect(() => {
    if (formData.state) {
      loadDistricts(formData.state)
    }
  }, [formData.state])

  const loadInitialData = async () => {
    try {
      const states = await getStates()
      const crops = await getCrops()

      setDropdownData({
        ...dropdownData,
        states: states.map((state) => ({ label: state.name, value: state.id })),
        crops: crops.map((crop) => ({ label: crop.name, value: crop.id })),
      })
    } catch (error) {
      console.error("Error loading initial data:", error)
    }
  }

  const loadDistricts = async (stateId) => {
    try {
      const districts = await getDistricts(stateId)
      setDropdownData({
        ...dropdownData,
        districts: districts.map((district) => ({ label: district.name, value: district.id })),
      })
    } catch (error) {
      console.error("Error loading districts:", error)
    }
  }

  const convertToKgHa = (value) => {
    const numValue = Number.parseFloat(value) || 0
    return (numValue * 2.24).toFixed(2)
  }

  const handleInputChange = (field, value) => {
    setFormData({ ...formData, [field]: value })

    // Clear error for this field
    if (errors[field]) {
      setErrors({ ...errors, [field]: null })
    }

    // Update conversion for NPK values
    if (field === "nitrogen") {
      setConversionData({ ...conversionData, nitrogenKgHa: convertToKgHa(value) })
    } else if (field === "phosphorus") {
      setConversionData({ ...conversionData, phosphorusKgHa: convertToKgHa(value) })
    } else if (field === "potassium") {
      setConversionData({ ...conversionData, potassiumKgHa: convertToKgHa(value) })
    }
  }

  const validateForm = () => {
    const newErrors = {}

    if (!formData.nitrogen || isNaN(Number.parseFloat(formData.nitrogen))) {
      newErrors.nitrogen = "Please enter a valid nitrogen value"
    }
    if (!formData.phosphorus || isNaN(Number.parseFloat(formData.phosphorus))) {
      newErrors.phosphorus = "Please enter a valid phosphorus value"
    }
    if (!formData.potassium || isNaN(Number.parseFloat(formData.potassium))) {
      newErrors.potassium = "Please enter a valid potassium value"
    }
    if (
      !formData.ph ||
      isNaN(Number.parseFloat(formData.ph)) ||
      Number.parseFloat(formData.ph) < 0 ||
      Number.parseFloat(formData.ph) > 14
    ) {
      newErrors.ph = "Please enter a valid pH value (0-14)"
    }
    if (!formData.plotArea || isNaN(Number.parseFloat(formData.plotArea))) {
      newErrors.plotArea = "Please enter a valid plot area"
    }
    if (!formData.state) {
      newErrors.state = "Please select a state"
    }
    if (!formData.district) {
      newErrors.district = "Please select a district"
    }
    if (!formData.crop) {
      newErrors.crop = "Please select a crop"
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleAnalyze = () => {
    if (validateForm()) {
      navigation.navigate("Results", {
        formData: {
          ...formData,
          conversionData,
        },
      })
    } else {
      Alert.alert("Validation Error", "Please fill all fields correctly")
    }
  }

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Title style={styles.headerTitle}>Soil Analysis</Title>
        <Text style={styles.headerSubtitle}>Enter your soil test values to get personalized recommendations</Text>
      </View>

      <Card style={styles.card}>
        <Card.Content>
          <Title style={styles.sectionTitle}>NPK Values (mg/kg)</Title>

          <View style={styles.inputContainer}>
            <TextInput
              label="Nitrogen (N)"
              value={formData.nitrogen}
              onChangeText={(value) => handleInputChange("nitrogen", value)}
              keyboardType="numeric"
              mode="outlined"
              error={!!errors.nitrogen}
              right={<TextInput.Affix text="mg/kg" />}
            />
            <HelperText type="error" visible={!!errors.nitrogen}>
              {errors.nitrogen}
            </HelperText>
            <Text style={styles.conversionText}>= {conversionData.nitrogenKgHa} kg/ha</Text>
          </View>

          <View style={styles.inputContainer}>
            <TextInput
              label="Phosphorus (P)"
              value={formData.phosphorus}
              onChangeText={(value) => handleInputChange("phosphorus", value)}
              keyboardType="numeric"
              mode="outlined"
              error={!!errors.phosphorus}
              right={<TextInput.Affix text="mg/kg" />}
            />
            <HelperText type="error" visible={!!errors.phosphorus}>
              {errors.phosphorus}
            </HelperText>
            <Text style={styles.conversionText}>= {conversionData.phosphorusKgHa} kg/ha</Text>
          </View>

          <View style={styles.inputContainer}>
            <TextInput
              label="Potassium (K)"
              value={formData.potassium}
              onChangeText={(value) => handleInputChange("potassium", value)}
              keyboardType="numeric"
              mode="outlined"
              error={!!errors.potassium}
              right={<TextInput.Affix text="mg/kg" />}
            />
            <HelperText type="error" visible={!!errors.potassium}>
              {errors.potassium}
            </HelperText>
            <Text style={styles.conversionText}>= {conversionData.potassiumKgHa} kg/ha</Text>
          </View>

          <Divider style={styles.divider} />

          <View style={styles.inputContainer}>
            <TextInput
              label="Soil pH"
              value={formData.ph}
              onChangeText={(value) => handleInputChange("ph", value)}
              keyboardType="numeric"
              mode="outlined"
              error={!!errors.ph}
              placeholder="Enter pH value (1-14)"
            />
            <HelperText type="error" visible={!!errors.ph}>
              {errors.ph}
            </HelperText>
          </View>

          <View style={styles.inputContainer}>
            <TextInput
              label="Plot Area"
              value={formData.plotArea}
              onChangeText={(value) => handleInputChange("plotArea", value)}
              keyboardType="numeric"
              mode="outlined"
              error={!!errors.plotArea}
            />
            <HelperText type="error" visible={!!errors.plotArea}>
              {errors.plotArea}
            </HelperText>
          </View>

          <View style={styles.pickerContainer}>
            <Text style={styles.pickerLabel}>Area Unit</Text>
            <RNPickerSelect
              onValueChange={(value) => handleInputChange("areaUnit", value)}
              items={[
                { label: "Hectare", value: "hectare" },
                { label: "Acre", value: "acre" },
              ]}
              value={formData.areaUnit}
              style={pickerSelectStyles}
            />
          </View>

          <Divider style={styles.divider} />

          <Title style={styles.sectionTitle}>Location & Crop Details</Title>

          <View style={styles.pickerContainer}>
            <Text style={styles.pickerLabel}>State</Text>
            <RNPickerSelect
              onValueChange={(value) => handleInputChange("state", value)}
              items={dropdownData.states}
              value={formData.state}
              placeholder={{ label: "Select State", value: "" }}
              style={pickerSelectStyles}
            />
            <HelperText type="error" visible={!!errors.state}>
              {errors.state}
            </HelperText>
          </View>

          <View style={styles.pickerContainer}>
            <Text style={styles.pickerLabel}>District</Text>
            <RNPickerSelect
              onValueChange={(value) => handleInputChange("district", value)}
              items={dropdownData.districts}
              value={formData.district}
              placeholder={{ label: "Select District", value: "" }}
              style={pickerSelectStyles}
              disabled={!formData.state}
            />
            <HelperText type="error" visible={!!errors.district}>
              {errors.district}
            </HelperText>
          </View>

          <View style={styles.pickerContainer}>
            <Text style={styles.pickerLabel}>Crop</Text>
            <RNPickerSelect
              onValueChange={(value) => handleInputChange("crop", value)}
              items={dropdownData.crops}
              value={formData.crop}
              placeholder={{ label: "Select Crop", value: "" }}
              style={pickerSelectStyles}
            />
            <HelperText type="error" visible={!!errors.crop}>
              {errors.crop}
            </HelperText>
          </View>

          <Button
            mode="contained"
            onPress={handleAnalyze}
            style={styles.analyzeButton}
            labelStyle={styles.analyzeButtonText}
          >
            Analyze Soil
          </Button>
        </Card.Content>
      </Card>
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
    elevation: 4,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: "bold",
    marginBottom: 15,
    color: "#4CAF50",
  },
  inputContainer: {
    marginBottom: 15,
  },
  conversionText: {
    fontSize: 14,
    color: "#4CAF50",
    fontWeight: "bold",
    marginTop: 5,
    textAlign: "right",
  },
  divider: {
    marginVertical: 20,
  },
  pickerContainer: {
    marginBottom: 15,
  },
  pickerLabel: {
    fontSize: 16,
    fontWeight: "bold",
    marginBottom: 8,
    color: "#333",
  },
  analyzeButton: {
    marginTop: 20,
    paddingVertical: 8,
    backgroundColor: "#4CAF50",
  },
  analyzeButtonText: {
    fontSize: 16,
    fontWeight: "bold",
  },
})

const pickerSelectStyles = StyleSheet.create({
  inputIOS: {
    fontSize: 16,
    paddingVertical: 12,
    paddingHorizontal: 10,
    borderWidth: 1,
    borderColor: "#CCC",
    borderRadius: 4,
    color: "black",
    paddingRight: 30,
    backgroundColor: "white",
  },
  inputAndroid: {
    fontSize: 16,
    paddingHorizontal: 10,
    paddingVertical: 8,
    borderWidth: 1,
    borderColor: "#CCC",
    borderRadius: 4,
    color: "black",
    paddingRight: 30,
    backgroundColor: "white",
  },
})
