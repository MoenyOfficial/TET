import { useState } from 'react'

function UserSearch({ onFriendAdded }) {
  const [searchTerm, setSearchTerm] = useState('')
  const [searchResults, setSearchResults] = useState([])
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')
  const [addingFriend, setAddingFriend] = useState(null)

  const handleSearch = async (e) => {
    e.preventDefault()
    
    if (!searchTerm.trim()) return
    
    setLoading(true)
    setError('')
    
    try {
      const response = await fetch(`https://test.wesveld.nl/users/search.php?q=${encodeURIComponent(searchTerm)}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      })
      
      if (response.ok) {
        const data = await response.json()
        setSearchResults(data)
      } else {
        throw new Error('Search failed')
      }
    } catch (error) {
      setError('Error searching for users')
      console.error(error)
    } finally {
      setLoading(false)
    }
  }

  const addFriend = async (userId) => {
    setAddingFriend(userId)
    
    try {
      const response = await fetch('https://test.wesveld.nl/friends/add.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({
          friend_id: userId
        })
      })
      
      const data = await response.json()
      
      if (response.ok) {
        // Update the search results to show added
        setSearchResults(searchResults.map(user => 
          user.id === userId ? { ...user, added: true } : user
        ))
        
        // Notify parent component
        if (onFriendAdded) {
          onFriendAdded()
        }
      } else {
        throw new Error(data.message || 'Failed to add friend')
      }
    } catch (error) {
      setError(error.message || 'Error adding friend')
      console.error(error)
    } finally {
      setAddingFriend(null)
    }
  }

  return (
    <div className="user-search">
      <h2>Find Users</h2>
      
      <form onSubmit={handleSearch} className="search-form">
        <input
          type="text"
          placeholder="Search by username or email"
          value={searchTerm}
          onChange={(e) => setSearchTerm(e.target.value)}
        />
        <button type="submit" disabled={loading}>
          {loading ? 'Searching...' : 'Search'}
        </button>
      </form>
      
      {error && <p className="error-message">{error}</p>}
      
      <div className="search-results">
        {searchResults.length > 0 ? (
          <ul>
            {searchResults.map(user => (
              <li key={user.id} className="user-item">
                <div className="avatar">{user.username.charAt(0).toUpperCase()}</div>
                <div className="user-info">
                  <h3>{user.username}</h3>
                  <p>{user.email}</p>
                </div>
                <button 
                  className={`add-friend-btn ${user.added ? 'added' : ''}`}
                  onClick={() => addFriend(user.id)}
                  disabled={addingFriend === user.id || user.added}
                >
                  {addingFriend === user.id ? 'Adding...' : user.added ? 'Added' : 'Add Friend'}
                </button>
              </li>
            ))}
          </ul>
        ) : searchTerm && !loading ? (
          <p>No users found. Try a different search term.</p>
        ) : null}
      </div>
    </div>
  )
}

export default UserSearch
