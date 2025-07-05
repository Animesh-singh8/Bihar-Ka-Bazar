-- Add Makhana and Maize products to the database
INSERT INTO products (farmer_id, category_id, title, description, price, unit, quantity_available, min_order_quantity, image, location, harvest_date, is_organic, certification) VALUES

-- Makhana (Fox Nuts)
(2, 4, 'Premium Bihar Makhana (Fox Nuts)', 'High-quality makhana (fox nuts) from the wetlands of Bihar. Rich in protein, calcium, and antioxidants. Perfect for snacking and cooking. Hand-picked and naturally processed.', 450.00, 'kg', 150, 1, 'makhana.jpg', 'Darbhanga', '2024-01-15', true, 'Organic India Certified'),

(2, 4, 'Roasted Makhana Mix', 'Delicious roasted makhana with traditional spices. Ready-to-eat healthy snack. No artificial preservatives or colors. Perfect for health-conscious consumers.', 380.00, 'kg', 80, 1, 'roasted-makhana.jpg', 'Madhubani', '2024-01-20', true, 'NPOP Certified'),

-- Maize (Corn)
(2, 4, 'Fresh Sweet Corn', 'Sweet and tender corn kernels freshly harvested from Bihar farms. Perfect for cooking, salads, and snacks. High in fiber and vitamins.', 45.00, 'kg', 500, 2, 'sweet-corn.jpg', 'Begusarai', '2024-02-10', false, ''),

(2, 4, 'Organic Yellow Maize', 'Premium quality organic yellow maize. Ideal for animal feed and human consumption. Naturally grown without chemicals. High nutritional value.', 35.00, 'kg', 800, 5, 'yellow-maize.jpg', 'Samastipur', '2024-02-05', true, 'Organic India Certified'),

(2, 4, 'Popcorn Corn Kernels', 'Special variety corn kernels perfect for making popcorn. High expansion rate and excellent taste. Great for home and commercial use.', 65.00, 'kg', 200, 2, 'popcorn-corn.jpg', 'Muzaffarpur', '2024-02-08', false, ''),

-- Additional Bihar Specialties
(2, 1, 'Bihar Kesar Mango', 'Rare Kesar variety mango from Bihar. Extremely sweet and aromatic. Limited seasonal availability. Premium quality for special occasions.', 350.00, 'kg', 100, 1, 'kesar-mango.jpg', 'Bhagalpur', '2024-06-10', true, 'Organic India Certified'),

(2, 3, 'Fresh Bottle Gourd (Lauki)', 'Fresh bottle gourd from Bihar farms. Rich in water content and nutrients. Perfect for healthy cooking and diet plans.', 25.00, 'kg', 300, 2, 'bottle-gourd.jpg', 'Patna', '2024-02-20', false, ''),

(2, 3, 'Organic Bitter Gourd (Karela)', 'Organic bitter gourd known for its medicinal properties. Helps in blood sugar control. Freshly harvested and chemical-free.', 55.00, 'kg', 150, 1, 'bitter-gourd.jpg', 'Nalanda', '2024-02-18', true, 'NPOP Certified'),

(2, 6, 'Bihar Red Chili Powder', 'Traditional red chili powder made from Bihar chilies. Perfect spice level with authentic flavor. Ground using traditional methods.', 180.00, 'kg', 100, 1, 'red-chili-powder.jpg', 'Gaya', '2024-01-25', false, ''),

(2, 6, 'Organic Turmeric Powder', 'Pure organic turmeric powder from Bihar farms. High curcumin content. Known for its medicinal and culinary properties.', 220.00, 'kg', 80, 1, 'turmeric-powder.jpg', 'Rohtas', '2024-01-30', true, 'Organic India Certified');
