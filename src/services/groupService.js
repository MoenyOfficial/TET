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

export const groupService = {
  getGroups: async () => {
    const response = await axios.get(`${API_URL}/groups/list.php`, getAuthHeader());
    return response.data;
  },

  createGroup: async (name, description) => {
    const response = await axios.post(`${API_URL}/groups/create.php`, {
      name,
      description
    }, getAuthHeader());
    return response.data;
  },

  getGroupDetails: async (groupId) => {
    const response = await axios.get(`${API_URL}/groups/details.php?group_id=${groupId}`, getAuthHeader());
    return response.data;
  },

  addMember: async (groupId, userId) => {
    const response = await axios.post(`${API_URL}/groups/add_member.php`, {
      group_id: groupId,
      user_id: userId
    }, getAuthHeader());
    return response.data;
  },

  removeMember: async (groupId, userId) => {
    const response = await axios.post(`${API_URL}/groups/remove_member.php`, {
      group_id: groupId,
      user_id: userId
    }, getAuthHeader());
    return response.data;
  }
};
