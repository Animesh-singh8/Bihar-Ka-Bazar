"use client"

import { useState } from "react"
import { View, Text, StyleSheet, ScrollView } from "react-native"
import { TextInput, Button, Card, Title, Paragraph, HelperText, Divider } from "react-native-paper"
import Icon from "react-native-vector-icons/MaterialCommunityIcons"

export default function CalculatorScreen() {
  const [inputs, setInputs] = useState({
    currentN: "",
    currentP: "",
    currentK: "",
    requiredN: "",
    requiredP: "",
    requiredK: "",
    plotArea: "",
  })

  const [results, setResults] = useState(null)
  const [errors, setErrors] = useState({})

  const handleInputChange = (field, value) => {
    setInputs({ ...inputs, [field]: value })

    // Clear error for this field
    if (errors[field]) {
      setErrors({ ...errors, [field]: null })
    }
  }

  const validateInputs = () => {
    const newErrors = {}

    Object.keys(inputs).forEach((key) => {
      if (!inputs[key] || isNaN(Number.parseFloat(inputs[key]))) {
        newErrors[key] = "Please enter a valid number"
      }
    })

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const calculateFertilizers = () => {
    if (!validateInputs()) return

    const currentN = Number.parseFloat(inputs.currentN)
    const currentP = Number.parseFloat(inputs.currentP)
    const currentK = Number.parseFloat(inputs.currentK)
    const requiredN = Number.parseFloat(inputs.requiredN)
    const requiredP = Number.parseFloat(inputs.requiredP)
    const requiredK = Number.parseFloat(inputs.requiredK)
    const plotArea = Number.parseFloat(inputs.plotArea)

    // Calculate deficiencies
    const nDeficiency = Math.max(0, requiredN - currentN)
    const pDeficiency = Math.max(0, requiredP - currentP)
    const kDeficiency = Math.max(0, requiredK - currentK)

    // Calculate fertilizer requirements per hectare
    const ureaPerHa = nDeficiency > 0 ? (nDeficiency * 100) / 46 : 0 // Urea has 46% N
    const dapPerHa = pDeficiency > 0 ? (pDeficiency * 100) / 46 : 0 // DAP has 46% P
    const mopPerHa = kDeficiency > 0 ? (kDeficiency * 100) / 60 : 0 // MOP has 60% K

    // Calculate total fertilizer needed
    const totalUrea = ureaPerHa * plotArea
    const totalDAP = dapPerHa * plotArea
    const totalMOP = mopPerHa * plotArea

    // Calculate approximate costs (these are sample prices)
    const ureaCost = totalUrea * 6 // ₹6 per kg
    const dapCost = totalDAP * 24 // ₹24 per kg
    const mopCost = totalMOP * 17 // ₹17 per kg
    const totalCost = ureaCost + dapCost + mopCost

    setResults({
      deficiencies: { nDeficiency, pDeficiency, kDeficiency },
      fertilizers: {
        urea: { perHa: ureaPerHa, total: totalUrea, cost: ureaCost },
        dap: { perHa: dapPerHa, total: totalDAP, cost: dapCost },
        mop: { perHa: mopPerHa, total: totalMOP, cost: mopCost },
      },
      totalCost,
      plotArea,
    })
  }

  const resetCalculator = () => {
    setInputs({
      currentN: "",
      currentP: "",
      currentK: "",
      requiredN: "",
      requiredP: "",
      requiredK: "",
      plotArea: "",
    })
    setResults(null)
    setErrors({})
  }

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Title style={styles.headerTitle}>Fertilizer Calculator</Title>
        <Text style={styles.headerSubtitle}>Calculate exact fertilizer requirements for your soil</Text>
      </View>

      <Card style={styles.card}>
        <Card.Content>
          <Title style={styles.sectionTitle}>Current NPK Values (kg/ha)</Title>

          <View style={styles.inputRow}>
            <View style={styles.inputContainer}>
              <TextInput
                label="Current Nitrogen"
                value={inputs.currentN}
                onChangeText={(value) => handleInputChange("currentN", value)}
                keyboardType="numeric"
                mode="outlined"
                error={!!errors.currentN}
                right={<TextInput.Affix text="kg/ha" />}
              />
              <HelperText type="error" visible={!!errors.currentN}>
                {errors.currentN}
              </HelperText>
            </View>

            <View style={styles.inputContainer}>
              <TextInput
                label="Current Phosphorus"
                value={inputs.currentP}
                onChangeText={(value) => handleInputChange("currentP", value)}
                keyboardType="numeric"
                mode="outlined"
                error={!!errors.currentP}
                right={<TextInput.Affix text="kg/ha" />}
              />
              <HelperText type="error" visible={!!errors.currentP}>
                {errors.currentP}
              </HelperText>
            </View>
          </View>

          <View style={styles.inputContainer}>
            <TextInput
              label="Current Potassium"
              value={inputs.currentK}
              onChangeText={(value) => handleInputChange("currentK", value)}
              keyboardType="numeric"
              mode="outlined"
              error={!!errors.currentK}
              right={<TextInput.Affix text="kg/ha" />}
            />
            <HelperText type="error" visible={!!errors.currentK}>
              {errors.currentK}
            </HelperText>
          </View>

          <Divider style={styles.divider} />

          <Title style={styles.sectionTitle}>Required NPK Values (kg/ha)</Title>

          <View style={styles.inputRow}>
            <View style={styles.inputContainer}>
              <TextInput
                label="Required Nitrogen"
                value={inputs.requiredN}
                onChangeText={(value) => handleInputChange("requiredN", value)}
                keyboardType="numeric"
                mode="outlined"
                error={!!errors.requiredN}
                right={<TextInput.Affix text="kg/ha" />}
              />
              <HelperText type="error" visible={!!errors.requiredN}>
                {errors.requiredN}
              </HelperText>
            </View>

            <View style={styles.inputContainer}>
              <TextInput
                label="Required Phosphorus"
                value={inputs.requiredP}
                onChangeText={(value) => handleInputChange("requiredP", value)}
                keyboardType="numeric"
                mode="outlined"
                error={!!errors.requiredP}
                right={<TextInput.Affix text="kg/ha" />}
              />
              <HelperText type="error" visible={!!errors.requiredP}>
                {errors.requiredP}
              </HelperText>
            </View>
          </View>

          <View style={styles.inputContainer}>
            <TextInput
              label="Required Potassium"
              value={inputs.requiredK}
              onChangeText={(value) => handleInputChange("requiredK", value)}
              keyboardType="numeric"
              mode="outlined"
              error={!!errors.requiredK}
              right={<TextInput.Affix text="kg/ha" />}
            />
            <HelperText type="error" visible={!!errors.requiredK}>
              {errors.requiredK}
            </HelperText>
          </View>

          <Divider style={styles.divider} />

          <View style={styles.inputContainer}>
            <TextInput
              label="Plot Area"
              value={inputs.plotArea}
              onChangeText={(value) => handleInputChange("plotArea", value)}
              keyboardType="numeric"
              mode="outlined"
              error={!!errors.plotArea}
              right={<TextInput.Affix text="hectares" />}
            />
            <HelperText type="error" visible={!!errors.plotArea}>
              {errors.plotArea}
            </HelperText>
          </View>

          <View style={styles.buttonRow}>
            <Button mode="contained" onPress={calculateFertilizers} style={styles.calculateButton} icon="calculator">
              Calculate
            </Button>
            <Button mode="outlined" onPress={resetCalculator} style={styles.resetButton} icon="refresh">
              Reset
            </Button>
          </View>
        </Card.Content>
      </Card>

      {results && (
        <Card style={styles.card}>
          <Card.Content>
            <Title style={styles.sectionTitle}>Calculation Results</Title>

            {/* Deficiencies */}
            <View style={styles.resultSection}>
              <Text style={styles.resultSubtitle}>Nutrient Deficiencies</Text>
              <View style={styles.deficiencyRow}>
                <Text style={styles.deficiencyText}>Nitrogen: {results.deficiencies.nDeficiency.toFixed(2)} kg/ha</Text>
                <Text style={styles.deficiencyText}>
                  Phosphorus: {results.deficiencies.pDeficiency.toFixed(2)} kg/ha
                </Text>
                <Text style={styles.deficiencyText}>
                  Potassium: {results.deficiencies.kDeficiency.toFixed(2)} kg/ha
                </Text>
              </View>
            </View>

            <Divider style={styles.divider} />

            {/* Fertilizer Requirements */}
            <View style={styles.resultSection}>
              <Text style={styles.resultSubtitle}>Fertilizer Requirements</Text>

              {results.fertilizers.urea.total > 0 && (
                <Card style={styles.fertilizerResultCard}>
                  <Card.Content>
                    <View style={styles.fertilizerHeader}>
                      <Icon name="flask" size={24} color="#4CAF50" />
                      <Title style={styles.fertilizerTitle}>Urea</Title>
                    </View>
                    <Paragraph>Per hectare: {results.fertilizers.urea.perHa.toFixed(2)} kg</Paragraph>
                    <Paragraph style={styles.totalAmount}>
                      Total needed: {results.fertilizers.urea.total.toFixed(2)} kg
                    </Paragraph>
                    <Paragraph style={styles.costText}>
                      Estimated cost: ₹{results.fertilizers.urea.cost.toFixed(0)}
                    </Paragraph>
                  </Card.Content>
                </Card>
              )}

              {results.fertilizers.dap.total > 0 && (
                <Card style={styles.fertilizerResultCard}>
                  <Card.Content>
                    <View style={styles.fertilizerHeader}>
                      <Icon name="flask" size={24} color="#4CAF50" />
                      <Title style={styles.fertilizerTitle}>DAP</Title>
                    </View>
                    <Paragraph>Per hectare: {results.fertilizers.dap.perHa.toFixed(2)} kg</Paragraph>
                    <Paragraph style={styles.totalAmount}>
                      Total needed: {results.fertilizers.dap.total.toFixed(2)} kg
                    </Paragraph>
                    <Paragraph style={styles.costText}>
                      Estimated cost: ₹{results.fertilizers.dap.cost.toFixed(0)}
                    </Paragraph>
                  </Card.Content>
                </Card>
              )}

              {results.fertilizers.mop.total > 0 && (
                <Card style={styles.fertilizerResultCard}>
                  <Card.Content>
                    <View style={styles.fertilizerHeader}>
                      <Icon name="flask" size={24} color="#4CAF50" />
                      <Title style={styles.fertilizerTitle}>MOP</Title>
                    </View>
                    <Paragraph>Per hectare: {results.fertilizers.mop.perHa.toFixed(2)} kg</Paragraph>
                    <Paragraph style={styles.totalAmount}>
                      Total needed: {results.fertilizers.mop.total.toFixed(2)} kg
                    </Paragraph>
                    <Paragraph style={styles.costText}>
                      Estimated cost: ₹{results.fertilizers.mop.cost.toFixed(0)}
                    </Paragraph>
                  </Card.Content>
                </Card>
              )}

              {/* Total Cost */}
              <Card style={styles.totalCostCard}>
                <Card.Content>
                  <View style={styles.totalCostHeader}>
                    <Icon name="currency-inr" size={24} color="#FF9800" />
                    <Title style={styles.totalCostTitle}>Total Estimated Cost</Title>
                  </View>
                  <Text style={styles.totalCostAmount}>₹{results.totalCost.toFixed(0)}</Text>
                  <Text style={styles.totalCostNote}>*Prices are approximate and may vary by location</Text>
                </Card.Content>
              </Card>
            </View>
          </Card.Content>
        </Card>
      )}
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
  inputRow: {
    flexDirection: "row",
    justifyContent: "space-between",
  },
  inputContainer: {
    flex: 1,
    marginBottom: 15,
    marginHorizontal: 5,
  },
  divider: {
    marginVertical: 20,
  },
  buttonRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    marginTop: 20,
  },
  calculateButton: {
    flex: 0.6,
    backgroundColor: "#4CAF50",
  },
  resetButton: {
    flex: 0.35,
  },
  resultSection: {
    marginBottom: 20,
  },
  resultSubtitle: {
    fontSize: 16,
    fontWeight: "bold",
    marginBottom: 10,
    color: "#333",
  },
  deficiencyRow: {
    backgroundColor: "#F8F9FA",
    padding: 15,
    borderRadius: 8,
  },
  deficiencyText: {
    fontSize: 14,
    marginBottom: 5,
    color: "#666",
  },
  fertilizerResultCard: {
    marginBottom: 10,
    backgroundColor: "#F8F9FA",
  },
  fertilizerHeader: {
    flexDirection: "row",
    alignItems: "center",
    marginBottom: 10,
  },
  fertilizerTitle: {
    marginLeft: 10,
    fontSize: 16,
  },
  totalAmount: {
    fontWeight: "bold",
    color: "#4CAF50",
  },
  costText: {
    color: "#FF9800",
    fontWeight: "bold",
  },
  totalCostCard: {
    backgroundColor: "#FFF3E0",
    marginTop: 15,
  },
  totalCostHeader: {
    flexDirection: "row",
    alignItems: "center",
    marginBottom: 10,
  },
  totalCostTitle: {
    marginLeft: 10,
    fontSize: 18,
    color: "#FF9800",
  },
  totalCostAmount: {
    fontSize: 24,
    fontWeight: "bold",
    color: "#FF9800",
    textAlign: "center",
    marginBottom: 5,
  },
  totalCostNote: {
    fontSize: 12,
    color: "#666",
    textAlign: "center",
    fontStyle: "italic",
  },
})
