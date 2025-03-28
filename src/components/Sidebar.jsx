import { Link } from 'react-router-dom'

function Sidebar({ user, logout }) {
  return (
    <div className="sidebar">
      <div className="sidebar-header">
        <h1>WesChat</h1>
      </div>
      
      <div className="user-profile">
        <div className="avatar large">{user?.username?.charAt(0).toUpperCase()}</div>
        <h2>{user?.username}</h2>
        <p>{user?.email}</p>
      </div>
      
      <nav className="sidebar-nav">
        <Link to="/" className="nav-item">
          <i className="icon">🏠</i> Home
        </Link>
      </nav>
      
      {logout && (
        <button className="logout-button" onClick={logout}>
          Logout
        </button>
      )}
    </div>
  )
}

export default Sidebar
