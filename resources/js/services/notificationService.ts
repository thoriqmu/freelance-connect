import apiClient from './apiClient';

export const notificationService = {
  /**
   * Get user notifications
   */
  getUserNotifications(params: any = {}) {
    return apiClient.get('/notifications', { params });
  },

  /**
   * Mark notification as read
   */
  markAsRead(id: number) {
    return apiClient.patch(`/notifications/${id}/read`);
  },

  /**
   * Mark all notifications as read
   */
  markAllAsRead() {
    return apiClient.patch('/notifications/mark-all-read');
  }
};
