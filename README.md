# Lost & Found Tracker

A full-stack Lost & Found web application that helps users report, search, and recover lost items efficiently. The platform connects people who have lost items with those who have found them.

## Features

* User authentication (Login / Signup)
* Report lost and found items
* Browse all listed items
* Search and filter items
* View item details
* Claim/recover items system (if implemented)

## Tech Stack

* React (Frontend)
* Node.js (Backend)
* Express.js
* MongoDB
* REST API

## Installation

### Clone Repository

```bash id="c9k3aa"
git clone https://github.com/Sanila-rashid/lost-and-found-tracker.git
```

### Backend Setup

```bash id="p2k9lf"
cd backend
npm install
npm start
```

### Frontend Setup

```bash id="q7n2bb"
cd frontend
npm install
npm run dev
```

## Environment Variables

Create a `.env` file in backend:

```env id="env123"
PORT=5000
MONGO_URI=your_mongodb_connection_string
JWT_SECRET=your_secret_key
```

## Project Structure

```text id="structure1"
lost-and-found-tracker/
├── frontend/
├── backend/
├── README.md
```

## Future Improvements

* Real-time notifications
* Image upload for items
* Chat between finder and owner
* Map-based location tracking
* Admin dashboard

## Author

**Sanila Rashid**
