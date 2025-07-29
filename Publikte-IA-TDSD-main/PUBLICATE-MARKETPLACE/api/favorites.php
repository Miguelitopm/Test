

```css file="assets/css/buyer-profile.css" type="code" project="assets/css/buyer-profile"
[v0-no-op-code-block-prefix]/* assets/css/buyer-profile.css */

/* General Styles */
body {
  font-family: sans-serif;
  margin: 0;
  padding: 0;
  background-color: #f4f4f4;
}

.container {
  width: 80%;
  margin: 20px auto;
  background-color: #fff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

/* Navigation Tabs */
.nav-tabs {
  display: flex;
  border-bottom: 1px solid #ddd;
  margin-bottom: 20px;
}

.nav-tabs button {
  background-color: transparent;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 10px 20px;
  font-size: 16px;
  color: #333;
  border-bottom: 2px solid transparent;
  transition: all 0.3s ease;
}

.nav-tabs button:hover {
  color: #007bff;
}

.nav-tabs button.active {
  color: #007bff;
  border-bottom: 2px solid #007bff;
}

/* Order and Favorite Cards */
.card-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
}

.card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  padding: 20px;
}

.card h3 {
  margin-top: 0;
  margin-bottom: 10px;
  font-size: 20px;
}

.card p {
  margin-bottom: 10px;
  color: #555;
}

.card .order-details {
  margin-top: 15px;
  font-size: 14px;
}

.card .order-details strong {
  font-weight: bold;
}

/* Buyer Statistics */
.buyer-stats {
  margin-top: 30px;
  text-align: center;
}

.buyer-stats h2 {
  font-size: 24px;
  margin-bottom: 20px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
  text-align: center;
}

.stat-item {
  padding: 15px;
  background-color: #f9f9f9;
  border-radius: 5px;
}

.stat-item h3 {
  font-size: 18px;
  margin-bottom: 5px;
}

.stat-item p {
  font-size: 14px;
  color: #777;
}

/* Responsive Design */
@media (max-width: 768px) {
  .container {
    width: 95%;
  }

  .card-grid {
    grid-template-columns: 1fr;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }
}