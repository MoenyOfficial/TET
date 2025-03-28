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

export const chatService = {
  getMessages: async (userId) => {
    const response = await axios.get(`${API_URL}/messages/get.php?user_id=${userId}`, getAuthHeader());
    return response.data;
  },

  sendMessage: async (receiverId, content) => {
    const response = await axios.post(`${API_URL}/messages/send.php`, {
      receiver_id: receiverId,
      content
    }, getAuthHeader());
    return response.data;
  },

  getGroupMessages: async (groupId) => {
    const response = await axios.get(`${API_URL}/groups/messages.php?group_id=${groupId}`, getAuthHeader());
    return response.data;
  },

  sendGroupMessage: async (groupId, content) => {
    const response = await axios.post(`${API_URL}/groups/send_message.php`, {
      group_id: groupId,
      content
    }, getAuthHeader());
    return response.data;
  }
};
