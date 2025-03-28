import { useState, useEffect, useRef } from 'react'
import { useParams, Link } from 'react-router-dom'
import Sidebar from '../components/Sidebar'
import GroupMembers from '../components/GroupMembers'

function GroupChatPage({ user }) {
  const { groupId } = useParams()
  const [messages, setMessages] = useState([])
  const [newMessage, setNewMessage] = useState('')
  const [group, setGroup] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [showMembers, setShowMembers] = useState(false)
  const messagesEndRef = useRef(null)
  const messageInterval = useRef(null)

  useEffect(() => {
    fetchGroupDetails()
    fetchMessages()
    
    // Set up polling for new messages
    messageInterval.current = setInterval(fetchMessages, 5000)
    
    return () => {
      clearInterval(messageInterval.current)
    }
  }, [groupId])

  useEffect(() => {
    scrollToBottom()
  }, [messages])

  const fetchGroupDetails = async () => {
    try {
      const response = await fetch(`https://test.wesveld.nl/groups/list.php`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      })
      
      if (response.ok) {
        const data = await response.json()
        const foundGroup = data.find(g => g.id == groupId)
        if (foundGroup) {
          setGroup(foundGroup)
        }
      } else {
        throw new Error('Failed to fetch group details')
      }
    } catch (error) {
      setError('Error fetching group details')
      console.error(error)
    }
  }

  const fetchMessages = async () => {
    try {
      const response = await fetch(`https://test.wesveld.nl/groups/messages.php?group_id=${groupId}`, {
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
      const response = await fetch('https://test.wesveld.nl/groups/send_message.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({
          group_id: groupId,
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
          {group ? (
            <div className="chat-group-info">
              <div className="avatar group-avatar">{group.name.charAt(0).toUpperCase()}</div>
              <h2>{group.name}</h2>
              <button 
                className="members-button"
                onClick={() => setShowMembers(!showMembers)}
              >
                Members
              </button>
            </div>
          ) : (
            <h2>Group Chat</h2>
          )}
        </div>
        
        {showMembers && group && (
          <GroupMembers groupId={groupId} onClose={() => setShowMembers(false)} />
        )}
        
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
                  className={`message ${message.user_id == user.id ? 'sent' : 'received'}`}
                >
                  {message.user_id != user.id && (
                    <div className="message-sender">{message.username}</div>
                  )}
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

export default GroupChatPage
