import { useState, useEffect } from 'react'

function GroupMembers({ groupId, onClose }) {
  const [members, setMembers] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    fetchMembers()
  }, [groupId])

  const fetchMembers = async () => {
    try {
      const response = await fetch(`https://test.wesveld.nl/groups/members.php?group_id=${groupId}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      })
      
      if (response.ok) {
        const data = await response.json()
        setMembers(data)
      } else {
        throw new Error('Failed to fetch members')
      }
    } catch (error) {
      setError('Error fetching group members')
      console.error(error)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="members-modal">
      <div className="members-header">
        <h3>Group Members</h3>
        <button className="close-button" onClick={onClose}>×</button>
      </div>
      
      <div className="members-content">
        {loading ? (
          <p>Loading members...</p>
        ) : error ? (
          <p className="error-message">{error}</p>
        ) : members.length === 0 ? (
          <p>No members found.</p>
        ) : (
          <ul className="members-list">
            {members.map(member => (
              <li key={member.id} className="member-item">
                <div className="avatar">{member.username.charAt(0).toUpperCase()}</div>
                <div className="member-info">
                  <h4>{member.username}</h4>
                  <p>{member.email}</p>
                </div>
                {member.is_admin && (
                  <span className="admin-badge">Admin</span>
                )}
              </li>
            ))}
          </ul>
        )}
      </div>
    </div>
  )
}

export default GroupMembers
