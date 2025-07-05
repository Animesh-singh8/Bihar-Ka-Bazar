"use client"

import { useState, useEffect } from "react"
import { View, Text, StyleSheet, FlatList, TouchableOpacity } from "react-native"
import { Card, Title, Paragraph, Chip, FAB, Searchbar } from "react-native-paper"
import Icon from "react-native-vector-icons/MaterialCommunityIcons"
import { getSavedAnalyses, deleteSoilAnalysis } from "../database/database"

export default function HistoryScreen({ navigation }) {
  const [analyses, setAnalyses] = useState([])
  const [filteredAnalyses, setFilteredAnalyses] = useState([])
  const [searchQuery, setSearchQuery] = useState("")
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    loadAnalyses()
  }, [])

  useEffect(() => {
    filterAnalyses()
  }, [searchQuery, analyses])

  const loadAnalyses = async () => {
    try {
      const savedAnalyses = await getSavedAnalyses()
      setAnalyses(savedAnalyses)
      setLoading(false)
    } catch (error) {
      console.error("Error loading analyses:", error)
      setLoading(false)
    }
  }

  const filterAnalyses = () => {
    if (!searchQuery) {
      setFilteredAnalyses(analyses)
    } else {
      const filtered = analyses.filter(
        (analysis) =>
          analysis.crop.toLowerCase().includes(searchQuery.toLowerCase()) ||
          analysis.state.toLowerCase().includes(searchQuery.toLowerCase()) ||
          analysis.district.toLowerCase().includes(searchQuery.toLowerCase()),
      )
      setFilteredAnalyses(filtered)
    }
  }

  const handleDeleteAnalysis = async (id) => {
    try {
      await deleteSoilAnalysis(id)
      loadAnalyses()
    } catch (error) {
      console.error("Error deleting analysis:", error)
    }
  }

  const renderAnalysisItem = ({ item }) => (
    <Card style={styles.analysisCard}>
      <Card.Content>
        <View style={styles.analysisHeader}>
          <View style={styles.analysisInfo}>
            <Title style={styles.analysisTitle}>{item.crop}</Title>
            <Paragraph style={styles.analysisLocation}>
              {item.district}, {item.state}
            </Paragraph>
            <Paragraph style={styles.analysisDate}>{new Date(item.date).toLocaleDateString()}</Paragraph>
          </View>
          <View style={styles.analysisActions}>
            <TouchableOpacity
              onPress={() => navigation.navigate("Results", { formData: item })}
              style={styles.actionButton}
            >
              <Icon name="eye" size={24} color="#4CAF50" />
            </TouchableOpacity>
            <TouchableOpacity onPress={() => handleDeleteAnalysis(item.id)} style={styles.actionButton}>
              <Icon name="delete" size={24} color="#F44336" />
            </TouchableOpacity>
          </View>
        </View>

        <View style={styles.analysisDetails}>
          <View style={styles.npkValues}>
            <Chip mode="outlined" style={styles.npkChip}>
              N: {item.nitrogen} mg/kg
            </Chip>
            <Chip mode="outlined" style={styles.npkChip}>
              P: {item.phosphorus} mg/kg
            </Chip>
            <Chip mode="outlined" style={styles.npkChip}>
              K: {item.potassium} mg/kg
            </Chip>
          </View>
          <View style={styles.plotInfo}>
            <Text style={styles.plotText}>
              Plot: {item.plotArea} {item.areaUnit} | pH: {item.ph}
            </Text>
          </View>
        </View>
      </Card.Content>
    </Card>
  )

  const EmptyComponent = () => (
    <View style={styles.emptyContainer}>
      <Icon name="history" size={64} color="#CCC" />
      <Text style={styles.emptyTitle}>No Analysis History</Text>
      <Text style={styles.emptySubtitle}>Start by analyzing your soil to see results here</Text>
    </View>
  )

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Title style={styles.headerTitle}>Analysis History</Title>
        <Text style={styles.headerSubtitle}>View and manage your past soil analyses</Text>
      </View>

      <Searchbar
        placeholder="Search by crop, state, or district"
        onChangeText={setSearchQuery}
        value={searchQuery}
        style={styles.searchbar}
      />

      <FlatList
        data={filteredAnalyses}
        renderItem={renderAnalysisItem}
        keyExtractor={(item) => item.id.toString()}
        contentContainerStyle={styles.listContainer}
        ListEmptyComponent={EmptyComponent}
        refreshing={loading}
        onRefresh={loadAnalyses}
      />

      <FAB style={styles.fab} icon="plus" onPress={() => navigation.navigate("Analyze")} />
    </View>
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
  searchbar: {
    margin: 15,
    elevation: 2,
  },
  listContainer: {
    padding: 15,
    paddingTop: 0,
  },
  analysisCard: {
    marginBottom: 15,
    elevation: 2,
  },
  analysisHeader: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "flex-start",
    marginBottom: 15,
  },
  analysisInfo: {
    flex: 1,
  },
  analysisTitle: {
    fontSize: 18,
    fontWeight: "bold",
    marginBottom: 5,
  },
  analysisLocation: {
    fontSize: 14,
    color: "#666",
    marginBottom: 2,
  },
  analysisDate: {
    fontSize: 12,
    color: "#999",
  },
  analysisActions: {
    flexDirection: "row",
  },
  actionButton: {
    marginLeft: 10,
    padding: 5,
  },
  analysisDetails: {
    borderTopWidth: 1,
    borderTopColor: "#EEE",
    paddingTop: 15,
  },
  npkValues: {
    flexDirection: "row",
    flexWrap: "wrap",
    marginBottom: 10,
  },
  npkChip: {
    marginRight: 8,
    marginBottom: 5,
  },
  plotInfo: {
    alignItems: "flex-end",
  },
  plotText: {
    fontSize: 12,
    color: "#666",
  },
  emptyContainer: {
    flex: 1,
    justifyContent: "center",
    alignItems: "center",
    paddingTop: 100,
  },
  emptyTitle: {
    fontSize: 20,
    fontWeight: "bold",
    color: "#999",
    marginTop: 20,
  },
  emptySubtitle: {
    fontSize: 16,
    color: "#CCC",
    textAlign: "center",
    marginTop: 10,
    paddingHorizontal: 40,
  },
  fab: {
    position: "absolute",
    margin: 16,
    right: 0,
    bottom: 0,
    backgroundColor: "#4CAF50",
  },
})
