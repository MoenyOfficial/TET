import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import Sidebar from '../components/Sidebar'
import UserSearch from '../components/UserSearch'
import CreateGroup from '../components/CreateGroup'

function HomePage({ user, logout }) {
  const [activeTab, setActiveTab] = useState('friends')
  const [friends, setFriends] = useState([])
  const [groups, setGroups] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [showCreateGroup, setShowCreateGroup] = useState(false)

  useEffect(() => {
    fetchFriends()
    fetchGroups()
  }, [])

  const fetchFriends = async () => {
    try {
      const response = await fetch('https://test.wesveld.nl/friends/list.php', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      })
      
      if (response.ok) {
        const data = await response.json()
        setFriends(data)
      } else {
        throw new Error('Failed to fetch friends')
      }
    } catch (error) {
      setError('Error fetching friends')
      console.error(error)
    } finally {
      setLoading(false)
    }
  }

  const fetchGroups = async () => {
    try {
      const response = await fetch('https://test.wesveld.nl/groups/list.php', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      })
      
      if (response.ok) {
        const data = await response.json()
        setGroups(data)
      } else {
        throw new Error('Failed to fetch groups')
      }
    } catch (error) {
      setError('Error fetching groups')
      console.error(error)
    }
  }

  const handleGroupCreated = (newGroup) => {
    setGroups([...groups, newGroup])
    setShowCreateGroup(false)
  }

  const handleFriendAdded = () => {
    fetchFriends()
  }

  return (
    <div className="home-container">
      <Sidebar user={user} logout={logout} />
      
      <div className="main-content">
        <div className="tabs">
          <button 
            className={activeTab === 'friends' ? 'active' : ''} 
            onClick={() => setActiveTab('friends')}
          >
            Friends
          </button>
          <button 
            className={activeTab === 'groups' ? 'active' : ''} 
            onClick={() => setActiveTab('groups')}
          >
            Groups
          </button>
          <button 
            className={activeTab === 'search' ? 'active' : ''} 
            onClick={() => setActiveTab('search')}
          >
            Find Users
          </button>
        </div>
        
        <div className="tab-content">
          {activeTab === 'friends' && (
            <div className="friends-list">
              <h2>Your Friends</h2>
              {loading ? (
                <p>Loading friends...</p>
              ) : error ? (
                <p className="error-message">{error}</p>
              ) : friends.length === 0 ? (
                <p>You don't have any friends yet. Go to Find Users to add friends.</p>
              ) : (
                <ul>
                  {friends.map(friend => (
                    <li key={friend.id}>
                      <Link to={`/chat/${friend.id}`} className="friend-item">
                        <div className="avatar">{friend.username.charAt(0).toUpperCase()}</div>
                        <div className="friend-info">
                          <h3>{friend.username}</h3>
                          <p>{friend.email}</p>
                        </div>
                      </Link>
                    </li>
                  ))}
                </ul>
              )}
            </div>
          )}
          
          {activeTab === 'groups' && (
            <div className="groups-list">
              <div className="groups-header">
                <h2>Your Groups</h2>
                <button 
                  className="create-group-btn"
                  onClick={() => setShowCreateGroup(true)}
                >
                  Create Group
                </button>
              </div>
              
              {showCreateGroup && (
                <CreateGroup onGroupCreated={handleGroupCreated} onCancel={() => setShowCreateGroup(false)} />
              )}
              
              {loading ? (
                <p>Loading groups...</p>
              ) : groups.length === 0 ? (
                <p>You don't have any groups yet. Create a new group to get started.</p>
              ) : (
                <ul>
                  {groups.map(group => (
                    <li key={group.id}>
                      <Link to={`/group/${group.id}`} className="group-item">
                        <div className="avatar group-avatar">{group.name.charAt(0).toUpperCase()}</div>
                        <div className="group-info">
                          <h3>{group.name}</h3>
                          <p>{group.member_count} members</p>
                        </div>
                      </Link>
                    </li>
                  ))}
                </ul>
              )}
            </div>
          )}
          
          {activeTab === 'search' && (
            <UserSearch onFriendAdded={handleFriendAdded} />
          )}
        </div>
      </div>
    </div>
  )
}

export default HomePage
