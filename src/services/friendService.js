import axios from 'axios';

const API_URL = 'https://test.wesveld.nl';

const getAuthHeader = () => {
  const token = localStorage.getItem('token');
  return {
    headers: {
      Authorization: `Bearer ${token}`
    }
  };
};

export const friendService = {
  getFriends: async () => {
    const response = await axios.get(`${API_URL}/friends/list.php`, getAuthHeader());
    return response.data;
  },

  addFriend: async (userId) => {
    const response = await axios.post(`${API_URL}/friends/add.php`, {
      user_id: userId
    }, getAuthHeader());
    return response.data;
  },

  removeFriend: async (userId) => {
    const response = await axios.post(`${API_URL}/friends/remove.php`, {
      user_id: userId
    }, getAuthHeader());
    return response.data;
  },

  searchUsers: async (query) => {
    const response = await axios.get(`${API_URL}/users/search.php?q=${query}`, getAuthHeader());
    return response.data;
  }
};
