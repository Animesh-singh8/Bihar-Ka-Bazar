"use client"

import { useEffect, useState } from "react"
import { View, Text, StyleSheet, ScrollView, Alert } from "react-native"
import { Card, Title, Paragraph, Button, DataTable, Chip } from "react-native-paper"
import Icon from "react-native-vector-icons/MaterialCommunityIcons"
import { getCropRequirements, saveSoilAnalysis } from "../database/database"

export default function ResultsScreen({ route, navigation }) {
  const { formData } = route.params
  const [results, setResults] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    calculateResults()
  }, [])

  const calculateResults = async () => {
    try {
      // Get crop requirements from database
      const cropRequirements = await getCropRequirements(formData.crop, formData.state, formData.district)

      // Convert NPK values to kg/ha
      const nitrogenKgHa = Number.parseFloat(formData.conversionData.nitrogenKgHa)
      const phosphorusKgHa = Number.parseFloat(formData.conversionData.phosphorusKgHa)
      const potassiumKgHa = Number.parseFloat(formData.conversionData.potassiumKgHa)
      const ph = Number.parseFloat(formData.ph)

      // Calculate deficiencies
      const nDeficiency = Math.max(0, cropRequirements.required_n - nitrogenKgHa)
      const pDeficiency = Math.max(0, cropRequirements.required_p - phosphorusKgHa)
      const kDeficiency = Math.max(0, cropRequirements.required_k - potassiumKgHa)

      // Calculate fertilizer requirements
      const ureaRequired = nDeficiency > 0 ? (nDeficiency * 100) / 46 : 0 // Urea has 46% N
      const dapRequired = pDeficiency > 0 ? (pDeficiency * 100) / 46 : 0 // DAP has 46% P
      const mopRequired = kDeficiency > 0 ? (kDeficiency * 100) / 60 : 0 // MOP has 60% K

      // Convert area to hectares if needed
      let plotAreaHa = Number.parseFloat(formData.plotArea)
      if (formData.areaUnit === "acre") {
        plotAreaHa = plotAreaHa * 0.404686
      }

      // Calculate total fertilizer needed
      const totalUrea = ureaRequired * plotAreaHa
      const totalDAP = dapRequired * plotAreaHa
      const totalMOP = mopRequired * plotAreaHa

      // pH status
      let phStatus = "Optimal"
      let phRecommendation = "pH is in optimal range. No adjustment needed."

      if (ph < cropRequirements.min_ph) {
        phStatus = "Acidic"
        phRecommendation = "Apply agricultural lime to increase soil pH."
      } else if (ph > cropRequirements.max_ph) {
        phStatus = "Alkaline"
        phRecommendation = "Apply agricultural sulfur or gypsum to decrease soil pH."
      }

      const calculatedResults = {
        measured: {
          nitrogen: nitrogenKgHa,
          phosphorus: phosphorusKgHa,
          potassium: potassiumKgHa,
          ph: ph,
        },
        required: {
          nitrogen: cropRequirements.required_n,
          phosphorus: cropRequirements.required_p,
          potassium: cropRequirements.required_k,
          minPh: cropRequirements.min_ph,
          maxPh: cropRequirements.max_ph,
        },
        deficiencies: {
          nitrogen: nDeficiency,
          phosphorus: pDeficiency,
          potassium: kDeficiency,
        },
        fertilizers: {
          urea: { perHa: ureaRequired, total: totalUrea },
          dap: { perHa: dapRequired, total: totalDAP },
          mop: { perHa: mopRequired, total: totalMOP },
        },
        ph: {
          status: phStatus,
          recommendation: phRecommendation,
        },
        plotArea: {
          value: Number.parseFloat(formData.plotArea),
          unit: formData.areaUnit,
          hectares: plotAreaHa,
        },
        cropRequirements,
      }

      setResults(calculatedResults)
      setLoading(false)

      // Save analysis to database
      await saveSoilAnalysis({
        ...formData,
        results: calculatedResults,
        date: new Date().toISOString(),
      })
    } catch (error) {
      console.error("Error calculating results:", error)
      Alert.alert("Error", "Failed to calculate results. Please try again.")
      setLoading(false)
    }
  }

  const getStatusColor = (measured, required, isHigherBetter = true) => {
    const ratio = measured / required
    if (isHigherBetter) {
      if (ratio >= 0.8 && ratio <= 1.2) return "#4CAF50" // Optimal
      if (ratio < 0.8) return "#F44336" // Deficient
      return "#FF9800" // Excess
    } else {
      return "#4CAF50" // For pH, we handle separately
    }
  }

  const getStatusText = (measured, required, isHigherBetter = true) => {
    const ratio = measured / required
    if (isHigherBetter) {
      if (ratio >= 0.8 && ratio <= 1.2) return "Optimal"
      if (ratio < 0.8) return `Deficient (${(required - measured).toFixed(2)} kg/ha needed)`
      return `Excess (${(measured - required).toFixed(2)} kg/ha extra)`
    } else {
      return "See pH section"
    }
  }

  if (loading || !results) {
    return (
      <View style={styles.loadingContainer}>
        <Text>Calculating results...</Text>
      </View>
    )
  }

  return (
    <ScrollView style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <Title style={styles.headerTitle}>Analysis Results</Title>
        <Text style={styles.headerSubtitle}>Your personalized soil analysis and fertilizer recommendations</Text>
      </View>

      {/* Summary Card */}
      <Card style={styles.card}>
        <Card.Content>
          <View style={styles.summaryRow}>
            <View style={styles.summaryLeft}>
              <Title>Analysis Summary</Title>
              <Text>
                Plot Area: {results.plotArea.value} {results.plotArea.unit}
              </Text>
              <Text>Date: {new Date().toLocaleDateString()}</Text>
            </View>
            <View style={styles.summaryRight}>
              <Button
                mode="outlined"
                onPress={() => {
                  /* TODO: Implement PDF generation */
                }}
                icon="file-pdf-box"
              >
                Download PDF
              </Button>
            </View>
          </View>
        </Card.Content>
      </Card>

      {/* NPK Comparison Table */}
      <Card style={styles.card}>
        <Card.Content>
          <Title style={styles.cardTitle}>NPK Comparison</Title>
          <DataTable>
            <DataTable.Header>
              <DataTable.Title>Parameter</DataTable.Title>
              <DataTable.Title numeric>Measured</DataTable.Title>
              <DataTable.Title numeric>Required</DataTable.Title>
              <DataTable.Title>Status</DataTable.Title>
            </DataTable.Header>

            <DataTable.Row>
              <DataTable.Cell>Nitrogen (N)</DataTable.Cell>
              <DataTable.Cell numeric>{results.measured.nitrogen.toFixed(2)}</DataTable.Cell>
              <DataTable.Cell numeric>{results.required.nitrogen.toFixed(2)}</DataTable.Cell>
              <DataTable.Cell>
                <Chip
                  mode="outlined"
                  textStyle={{
                    color: getStatusColor(results.measured.nitrogen, results.required.nitrogen),
                    fontSize: 10,
                  }}
                >
                  {getStatusText(results.measured.nitrogen, results.required.nitrogen).split("(")[0]}
                </Chip>
              </DataTable.Cell>
            </DataTable.Row>

            <DataTable.Row>
              <DataTable.Cell>Phosphorus (P)</DataTable.Cell>
              <DataTable.Cell numeric>{results.measured.phosphorus.toFixed(2)}</DataTable.Cell>
              <DataTable.Cell numeric>{results.required.phosphorus.toFixed(2)}</DataTable.Cell>
              <DataTable.Cell>
                <Chip
                  mode="outlined"
                  textStyle={{
                    color: getStatusColor(results.measured.phosphorus, results.required.phosphorus),
                    fontSize: 10,
                  }}
                >
                  {getStatusText(results.measured.phosphorus, results.required.phosphorus).split("(")[0]}
                </Chip>
              </DataTable.Cell>
            </DataTable.Row>

            <DataTable.Row>
              <DataTable.Cell>Potassium (K)</DataTable.Cell>
              <DataTable.Cell numeric>{results.measured.potassium.toFixed(2)}</DataTable.Cell>
              <DataTable.Cell numeric>{results.required.potassium.toFixed(2)}</DataTable.Cell>
              <DataTable.Cell>
                <Chip
                  mode="outlined"
                  textStyle={{
                    color: getStatusColor(results.measured.potassium, results.required.potassium),
                    fontSize: 10,
                  }}
                >
                  {getStatusText(results.measured.potassium, results.required.potassium).split("(")[0]}
                </Chip>
              </DataTable.Cell>
            </DataTable.Row>

            <DataTable.Row>
              <DataTable.Cell>pH Value</DataTable.Cell>
              <DataTable.Cell numeric>{results.measured.ph.toFixed(1)}</DataTable.Cell>
              <DataTable.Cell numeric>
                {results.required.minPh} - {results.required.maxPh}
              </DataTable.Cell>
              <DataTable.Cell>
                <Chip
                  mode="outlined"
                  textStyle={{
                    color: results.ph.status === "Optimal" ? "#4CAF50" : "#F44336",
                    fontSize: 10,
                  }}
                >
                  {results.ph.status}
                </Chip>
              </DataTable.Cell>
            </DataTable.Row>
          </DataTable>
        </Card.Content>
      </Card>

      {/* Fertilizer Recommendations */}
      <Card style={styles.card}>
        <Card.Content>
          <Title style={styles.cardTitle}>Fertilizer Recommendations</Title>

          {results.fertilizers.urea.perHa > 0 && (
            <Card style={styles.fertilizerCard}>
              <Card.Content>
                <View style={styles.fertilizerHeader}>
                  <Icon name="flask" size={24} color="#4CAF50" />
                  <Title style={styles.fertilizerTitle}>Urea (Nitrogen)</Title>
                </View>
                <Paragraph>Apply {results.fertilizers.urea.perHa.toFixed(2)} kg/ha</Paragraph>
                <Paragraph style={styles.totalAmount}>
                  Total for your plot: {results.fertilizers.urea.total.toFixed(2)} kg
                </Paragraph>
              </Card.Content>
            </Card>
          )}

          {results.fertilizers.dap.perHa > 0 && (
            <Card style={styles.fertilizerCard}>
              <Card.Content>
                <View style={styles.fertilizerHeader}>
                  <Icon name="flask" size={24} color="#4CAF50" />
                  <Title style={styles.fertilizerTitle}>DAP (Phosphorus)</Title>
                </View>
                <Paragraph>Apply {results.fertilizers.dap.perHa.toFixed(2)} kg/ha</Paragraph>
                <Paragraph style={styles.totalAmount}>
                  Total for your plot: {results.fertilizers.dap.total.toFixed(2)} kg
                </Paragraph>
              </Card.Content>
            </Card>
          )}

          {results.fertilizers.mop.perHa > 0 && (
            <Card style={styles.fertilizerCard}>
              <Card.Content>
                <View style={styles.fertilizerHeader}>
                  <Icon name="flask" size={24} color="#4CAF50" />
                  <Title style={styles.fertilizerTitle}>MOP (Potassium)</Title>
                </View>
                <Paragraph>Apply {results.fertilizers.mop.perHa.toFixed(2)} kg/ha</Paragraph>
                <Paragraph style={styles.totalAmount}>
                  Total for your plot: {results.fertilizers.mop.total.toFixed(2)} kg
                </Paragraph>
              </Card.Content>
            </Card>
          )}

          {/* Organic Alternative */}
          <Card style={styles.fertilizerCard}>
            <Card.Content>
              <View style={styles.fertilizerHeader}>
                <Icon name="leaf" size={24} color="#8BC34A" />
                <Title style={styles.fertilizerTitle}>Organic Alternative</Title>
              </View>
              <Paragraph>Apply 2-3 tons/ha of Vermicompost</Paragraph>
              <Paragraph style={styles.organicNote}>
                Organic manure improves soil structure and provides slow-release nutrients.
              </Paragraph>
            </Card.Content>
          </Card>
        </Card.Content>
      </Card>

      {/* pH Recommendation */}
      <Card style={styles.card}>
        <Card.Content>
          <Title style={styles.cardTitle}>pH Recommendation</Title>
          <View style={styles.phRecommendation}>
            <Icon
              name={results.ph.status === "Optimal" ? "check-circle" : "alert-circle"}
              size={24}
              color={results.ph.status === "Optimal" ? "#4CAF50" : "#F44336"}
            />
            <View style={styles.phText}>
              <Text style={styles.phStatus}>Status: {results.ph.status}</Text>
              <Text style={styles.phRecommendationText}>{results.ph.recommendation}</Text>
            </View>
          </View>
        </Card.Content>
      </Card>

      {/* Action Buttons */}
      <View style={styles.actionButtons}>
        <Button
          mode="outlined"
          onPress={() => navigation.navigate("Analyze")}
          style={styles.actionButton}
          icon="refresh"
        >
          New Analysis
        </Button>
        <Button mode="contained" onPress={() => navigation.navigate("Home")} style={styles.actionButton} icon="home">
          Back to Home
        </Button>
      </View>
    </ScrollView>
  )
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#FAFAFA",
  },
  loadingContainer: {
    flex: 1,
    justifyContent: "center",
    alignItems: "center",
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
  cardTitle: {
    fontSize: 18,
    fontWeight: "bold",
    marginBottom: 15,
    color: "#4CAF50",
  },
  summaryRow: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
  },
  summaryLeft: {
    flex: 1,
  },
  summaryRight: {
    marginLeft: 15,
  },
  fertilizerCard: {
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
  organicNote: {
    fontStyle: "italic",
    color: "#666",
    fontSize: 12,
  },
  phRecommendation: {
    flexDirection: "row",
    alignItems: "flex-start",
  },
  phText: {
    marginLeft: 10,
    flex: 1,
  },
  phStatus: {
    fontWeight: "bold",
    fontSize: 16,
    marginBottom: 5,
  },
  phRecommendationText: {
    color: "#666",
  },
  actionButtons: {
    flexDirection: "row",
    justifyContent: "space-around",
    padding: 20,
    paddingBottom: 40,
  },
  actionButton: {
    flex: 0.45,
  },
})
