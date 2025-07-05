import { View, Text, StyleSheet, ScrollView, Image, Dimensions } from "react-native"
import { Card, Button, Title, Paragraph } from "react-native-paper"
import Icon from "react-native-vector-icons/MaterialCommunityIcons"

const { width } = Dimensions.get("window")

export default function HomeScreen({ navigation }) {
  const features = [
    {
      icon: "flask",
      title: "Precise Analysis",
      description: "Get accurate NPK analysis based on scientific formulas",
    },
    {
      icon: "leaf",
      title: "Crop-Specific",
      description: "Recommendations tailored to your specific crop and region",
    },
    {
      icon: "currency-usd",
      title: "Cost-Effective",
      description: "Save money by applying only needed fertilizers",
    },
  ]

  const steps = [
    {
      number: "1",
      title: "Input NPK Values",
      description: "Enter your soil NPK values from test report",
    },
    {
      number: "2",
      title: "Select Location & Crop",
      description: "Choose your state, district, and crop type",
    },
    {
      number: "3",
      title: "Enter Plot Area",
      description: "Specify your land area for precise calculations",
    },
    {
      number: "4",
      title: "Get Recommendations",
      description: "Receive detailed fertilizer recommendations",
    },
  ]

  return (
    <ScrollView style={styles.container}>
      {/* Hero Section */}
      <View style={styles.heroSection}>
        <Image source={{ uri: "/placeholder.svg?height=200&width=400" }} style={styles.heroImage} resizeMode="cover" />
        <View style={styles.heroOverlay}>
          <Text style={styles.heroTitle}>Smart Soil Testing</Text>
          <Text style={styles.heroSubtitle}>Get precise NPK analysis and personalized fertilizer recommendations</Text>
          <Button
            mode="contained"
            onPress={() => navigation.navigate("Analyze")}
            style={styles.heroButton}
            labelStyle={styles.heroButtonText}
          >
            Analyze Your Soil
          </Button>
        </View>
      </View>

      {/* Features Section */}
      <View style={styles.section}>
        <Title style={styles.sectionTitle}>Why Choose Our App?</Title>
        <Text style={styles.sectionSubtitle}>Advanced tools for modern agriculture</Text>

        {features.map((feature, index) => (
          <Card key={index} style={styles.featureCard}>
            <Card.Content style={styles.featureContent}>
              <Icon name={feature.icon} size={40} color="#4CAF50" />
              <View style={styles.featureText}>
                <Title style={styles.featureTitle}>{feature.title}</Title>
                <Paragraph style={styles.featureDescription}>{feature.description}</Paragraph>
              </View>
            </Card.Content>
          </Card>
        ))}
      </View>

      {/* How It Works Section */}
      <View style={styles.section}>
        <Title style={styles.sectionTitle}>How It Works</Title>
        <Text style={styles.sectionSubtitle}>Simple steps to get your soil analysis</Text>

        {steps.map((step, index) => (
          <Card key={index} style={styles.stepCard}>
            <Card.Content style={styles.stepContent}>
              <View style={styles.stepNumber}>
                <Text style={styles.stepNumberText}>{step.number}</Text>
              </View>
              <View style={styles.stepText}>
                <Title style={styles.stepTitle}>{step.title}</Title>
                <Paragraph style={styles.stepDescription}>{step.description}</Paragraph>
              </View>
            </Card.Content>
          </Card>
        ))}
      </View>

      {/* CTA Section */}
      <View style={styles.ctaSection}>
        <Card style={styles.ctaCard}>
          <Card.Content style={styles.ctaContent}>
            <Title style={styles.ctaTitle}>Ready to optimize your crop yield?</Title>
            <Paragraph style={styles.ctaDescription}>Get started with our soil analysis tool today</Paragraph>
            <Button mode="contained" onPress={() => navigation.navigate("Analyze")} style={styles.ctaButton}>
              Start Analysis
            </Button>
          </Card.Content>
        </Card>
      </View>
    </ScrollView>
  )
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#FAFAFA",
  },
  heroSection: {
    height: 300,
    position: "relative",
  },
  heroImage: {
    width: "100%",
    height: "100%",
  },
  heroOverlay: {
    position: "absolute",
    top: 0,
    left: 0,
    right: 0,
    bottom: 0,
    backgroundColor: "rgba(0, 0, 0, 0.5)",
    justifyContent: "center",
    alignItems: "center",
    padding: 20,
  },
  heroTitle: {
    fontSize: 28,
    fontWeight: "bold",
    color: "white",
    textAlign: "center",
    marginBottom: 10,
  },
  heroSubtitle: {
    fontSize: 16,
    color: "white",
    textAlign: "center",
    marginBottom: 20,
    opacity: 0.9,
  },
  heroButton: {
    backgroundColor: "#4CAF50",
    paddingHorizontal: 20,
  },
  heroButtonText: {
    fontSize: 16,
    fontWeight: "bold",
  },
  section: {
    padding: 20,
  },
  sectionTitle: {
    fontSize: 24,
    fontWeight: "bold",
    textAlign: "center",
    marginBottom: 8,
    color: "#333",
  },
  sectionSubtitle: {
    fontSize: 16,
    textAlign: "center",
    color: "#666",
    marginBottom: 20,
  },
  featureCard: {
    marginBottom: 15,
    elevation: 2,
  },
  featureContent: {
    flexDirection: "row",
    alignItems: "center",
    padding: 10,
  },
  featureText: {
    flex: 1,
    marginLeft: 15,
  },
  featureTitle: {
    fontSize: 18,
    fontWeight: "bold",
    marginBottom: 5,
  },
  featureDescription: {
    fontSize: 14,
    color: "#666",
  },
  stepCard: {
    marginBottom: 15,
    elevation: 2,
  },
  stepContent: {
    flexDirection: "row",
    alignItems: "center",
    padding: 15,
  },
  stepNumber: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: "#4CAF50",
    justifyContent: "center",
    alignItems: "center",
    marginRight: 15,
  },
  stepNumberText: {
    color: "white",
    fontSize: 18,
    fontWeight: "bold",
  },
  stepText: {
    flex: 1,
  },
  stepTitle: {
    fontSize: 16,
    fontWeight: "bold",
    marginBottom: 5,
  },
  stepDescription: {
    fontSize: 14,
    color: "#666",
  },
  ctaSection: {
    padding: 20,
    paddingBottom: 40,
  },
  ctaCard: {
    backgroundColor: "#4CAF50",
    elevation: 4,
  },
  ctaContent: {
    padding: 20,
    alignItems: "center",
  },
  ctaTitle: {
    fontSize: 20,
    fontWeight: "bold",
    color: "white",
    textAlign: "center",
    marginBottom: 10,
  },
  ctaDescription: {
    fontSize: 16,
    color: "white",
    textAlign: "center",
    marginBottom: 20,
    opacity: 0.9,
  },
  ctaButton: {
    backgroundColor: "white",
  },
})
