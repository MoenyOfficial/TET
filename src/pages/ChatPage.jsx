import { useState, useEffect, useRef } from 'react'
import { useParams, Link } from 'react-router-dom'
import Sidebar from '../components/Sidebar'

function ChatPage({ user }) {
  const { userId } = useParams()
  const [messages, setMessages] = useState([])
  const [newMessage, setNewMessage] = useState('')
  const [friend, setFriend] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const messagesEndRef = useRef(null)
  const messageInterval = useRef(null)

  useEffect(() => {
    fetchFriendDetails()
    fetchMessages()
    
    // Set up polling for new messages
    messageInterval.current = setInterval(fetchMessages, 5000)
    
    return () => {
      clearInterval(messageInterval.current)
    }
  }, [userId])

  useEffect(() => {
    scrollToBottom()
  }, [messages])

  const fetchFriendDetails = async () => {
    try {
      const response = await fetch(`https://test.wesveld.nl/users/search.php?q=${userId}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      })
      
      if (response.ok) {
        const data = await response.json()
        if (data.length > 0) {
          setFriend(data[0])
        }
      } else {
        throw new Error('Failed to fetch friend details')
      }
    } catch (error) {
      setError('Error fetching friend details')
      console.error(error)
    }
  }

  const fetchMessages = async () => {
    try {
      const response = await fetch(`https://test.wesveld.nl/messages/get.php?user_id=${userId}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      })
      
      if (response.ok) {
        const data = await response.json()
        setMessages(data)
      } else {
        throw new Error('Failed to fetch messages')
      }
    } catch (error) {
      setError('Error fetching messages')
      console.error(error)
    } finally {
      setLoading(false)
    }
  }

  const sendMessage = async (e) => {
    e.preventDefault()
    
    if (!newMessage.trim()) return
    
    try {
      const response = await fetch('https://test.wesveld.nl/messages/send.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({
          receiver_id: userId,
          content: newMessage
        })
      })
      
      if (response.ok) {
        setNewMessage('')
        fetchMessages()
      } else {
        throw new Error('Failed to send message')
      }
    } catch (error) {
      setError('Error sending message')
      console.error(error)
    }
  }

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' })
  }

  return (
    <div className="chat-container">
      <Sidebar user={user} />
      
      <div className="chat-content">
        <div className="chat-header">
          <Link to="/" className="back-button">
            ← Back
          </Link>
          {friend ? (
            <div className="chat-user-info">
              <div className="avatar">{friend.username.charAt(0).toUpperCase()}</div>
              <h2>{friend.username}</h2>
            </div>
          ) : (
            <h2>Chat</h2>
          )}
        </div>
        
        <div className="messages-container">
          {loading ? (
            <p className="loading-messages">Loading messages...</p>
          ) : error ? (
            <p className="error-message">{error}</p>
          ) : messages.length === 0 ? (
            <p className="no-messages">No messages yet. Start the conversation!</p>
          ) : (
            <div className="messages-list">
              {messages.map(message => (
                <div 
                  key={message.id} 
                  className={`message ${message.sender_id == user.id ? 'sent' : 'received'}`}
                >
                  <div className="message-content">
                    {message.content}
                  </div>
                  <div className="message-time">
                    {new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                  </div>
                </div>
              ))}
              <div ref={messagesEndRef} />
            </div>
          )}
        </div>
        
        <form className="message-form" onSubmit={sendMessage}>
          <input
            type="text"
            placeholder="Type a message..."
            value={newMessage}
            onChange={(e) => setNewMessage(e.target.value)}
          />
          <button type="submit">Send</button>
        </form>
      </div>
    </div>
  )
}

export default ChatPage
