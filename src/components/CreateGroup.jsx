import { useState } from 'react'

function CreateGroup({ onGroupCreated, onCancel }) {
  const [name, setName] = useState('')
  const [description, setDescription] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  const handleSubmit = async (e) => {
    e.preventDefault()
    
    if (!name.trim()) return
    
    setLoading(true)
    setError('')
    
    try {
      const response = await fetch('https://test.wesveld.nl/groups/create.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({
          name,
          description
        })
      })
      
      const data = await response.json()
      
      if (response.ok) {
        // Create a new group object to pass back
        const newGroup = {
          id: data.group_id,
          name,
          description,
          member_count: 1,
          created_at: new Date().toISOString()
        }
        
        onGroupCreated(newGroup)
      } else {
        throw new Error(data.message || 'Failed to create group')
      }
    } catch (error) {
      setError(error.message || 'Error creating group')
      console.error(error)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="create-group-form">
      <h3>Create New Group</h3>
      
      {error && <p className="error-message">{error}</p>}
      
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="name">Group Name</label>
          <input
            type="text"
            id="name"
            value={name}
            onChange={(e) => setName(e.target.value)}
            required
          />
        </div>
        
        <div className="form-group">
          <label htmlFor="description">Description (Optional)</label>
          <textarea
            id="description"
            value={description}
            onChange={(e) => setDescription(e.target.value)}
            rows="3"
          />
        </div>
        
        <div className="form-buttons">
          <button type="button" onClick={onCancel} disabled={loading}>
            Cancel
          </button>
          <button type="submit" disabled={loading}>
            {loading ? 'Creating...' : 'Create Group'}
          </button>
        </div>
      </form>
    </div>
  )
}

export default CreateGroup
