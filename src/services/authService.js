import axios from 'axios';

const API_URL = 'https://test.wesveld.nl/';

export const authService = {
  login: async (email, password) => {
    const response = await axios.post(`${API_URL}/auth/login.php`, {
      email,
      password
    });
    return response.data;
  },

  register: async (userData) => {
    const response = await axios.post(`${API_URL}/auth/register.php`, userData);
    return response.data;
  },

  getCurrentUser: async () => {
    const token = localStorage.getItem('token');
    if (!token) return null;
    
    const response = await axios.get(`${API_URL}/auth/user.php`, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    });
    return response.data;
  }
};
