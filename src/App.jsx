import { useState, useEffect } from 'react'
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom'
import './App.css'

// Pages
import LoginPage from './pages/LoginPage'
import RegisterPage from './pages/RegisterPage'
import HomePage from './pages/HomePage'
import ChatPage from './pages/ChatPage'
import GroupChatPage from './pages/GroupChatPage'

function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(false)
  const [user, setUser] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Check if user is logged in
    const token = localStorage.getItem('token')
    if (token) {
      // Fetch user data
      fetch('https://test.wesveld.nl/auth/user.php', {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      .then(response => {
        if (response.ok) {
          return response.json()
        }
        throw new Error('Authentication failed')
      })
      .then(userData => {
        setUser(userData)
        setIsAuthenticated(true)
        setLoading(false)
      })
      .catch(error => {
        console.error('Error:', error)
        localStorage.removeItem('token')
        setIsAuthenticated(false)
        setLoading(false)
      })
    } else {
      setLoading(false)
    }
  }, [])

  const login = (userData, token) => {
    localStorage.setItem('token', token)
    setUser(userData)
    setIsAuthenticated(true)
  }

  const logout = () => {
    localStorage.removeItem('token')
    setUser(null)
    setIsAuthenticated(false)
  }

  if (loading) {
    return <div className="loading">Loading...</div>
  }

  return (
    <Router>
      <Routes>
        <Route path="/login" element={isAuthenticated ? <Navigate to="/" /> : <LoginPage login={login} />} />
        <Route path="/register" element={isAuthenticated ? <Navigate to="/" /> : <RegisterPage />} />
        <Route path="/" element={isAuthenticated ? <HomePage user={user} logout={logout} /> : <Navigate to="/login" />} />
        <Route path="/chat/:userId" element={isAuthenticated ? <ChatPage user={user} /> : <Navigate to="/login" />} />
        <Route path="/group/:groupId" element={isAuthenticated ? <GroupChatPage user={user} /> : <Navigate to="/login" />} />
      </Routes>
    </Router>
  )
}

export default App