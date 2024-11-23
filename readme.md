# NWooApp Plugin

NWooApp is a powerful WordPress plugin designed to transform your WordPress site into a fully functional native webview app with features like deep linking, push notifications, and order notifications.

---

## Features

- **Deep Linking**: Seamlessly navigate users to specific app pages using deep linking.
- **Push Notifications**:
  - Send notifications for WooCommerce order status changes.
  - Send topic-based notifications to users.
  - Store FCM tokens for each user upon login.
- **Order Notifications**:
  - Customize notifications for WooCommerce order statuses like processing, completed, refunded, etc.
  - Supports dynamic placeholders like `[order_id]` for personalized messages.
- **API Integration**:
  - Get accessible topics and external links via REST API.
  - API Endpoint: `/wp-json/nwooapp/v1/topics-and-links`.
- **Admin Panel**:
  - Manage topics and external links easily.
  - Configure WooCommerce order status notifications.
- **Logging**:
  - Push notifications are logged into a text file for debugging.

---

## Installation

1. Go to your WordPress Admin Dashboard.
2. Navigate to **Plugins** > **Add New**.
3. Search for **NWooApp** in the WordPress plugin store.
4. Click **Install Now**, then **Activate**.

## Usage

### Setting Up Push Notifications
1. Add FCM tokens via login form (handled automatically if implemented in your webview app).
2. Configure notification messages for WooCommerce order statuses from the plugin admin panel.
3. Use the topic management feature to manage notification topics.

### Accessing the API
- **Endpoint**: `/wp-json/nwooapp/v1/topics-and-links`.
- Sample Response:
  ```json
  {
    "topics": ["new-arrivals", "promotions"],
    "external_links": ["https://example.com/link1", "https://example.com/link2"]
  }