import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import {
  fetchUserNotifications,
  fetchLandlordNotifications,
  declineTourRequest,
  approveTourDate,
  deleteNotification,
} from 'services/NotificationService';

const initialState = {
  notifications: [],
  status: 'idle',
  error: null,
};

export const fetchUserNotificationsAsync = createAsyncThunk(
  'notifications/fetchRenterNotifications',
  async () => {
    return fetchUserNotifications();
  }
);

export const fetchLandlordNotificationsAsync = createAsyncThunk(
  'notifications/fetchLandlordNotifications',
  async () => {
    return fetchLandlordNotifications();
  }
);

export const declineTourAsync = createAsyncThunk(
  'notifications/declineTour',
  async ({ tourId, message }) => {
    return declineTourRequest(tourId, message);
  }
);

export const approveDateAsync = createAsyncThunk(
  'notifications/approveDate',
  async ({ tourId, selectedDate }, { rejectWithValue }) => {
    try {
      return approveTourDate({ tourId, selectedDate });
    } catch (error) {
      return rejectWithValue(error);
    }
  }
);

export const deleteNotificationAsync = createAsyncThunk(
  'notifications/deleteNotification',
  async (id, { rejectWithValue }) => {
    try {
      return deleteNotification(id);
    } catch (error) {
      return rejectWithValue(error);
    }
  }
);

const notificationsSlice = createSlice({
  name: 'notifications',
  initialState,
  reducers: {
    clearNotifications: (state) => {
      state.list = [];
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchUserNotificationsAsync.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(fetchUserNotificationsAsync.fulfilled, (state, action) => {
        state.status = 'succeeded';
        state.notifications = action.payload;
      })
      .addCase(fetchUserNotificationsAsync.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.error.message;
      })
      .addCase(fetchLandlordNotificationsAsync.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(fetchLandlordNotificationsAsync.fulfilled, (state, action) => {
        state.status = 'succeeded';
        state.notifications = action.payload;
      })
      .addCase(fetchLandlordNotificationsAsync.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.error.message;
      })
      .addCase(declineTourAsync.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(declineTourAsync.fulfilled, (state) => {
        state.status = 'declined';
      })
      .addCase(declineTourAsync.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.error.message;
      })
      .addCase(approveDateAsync.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(approveDateAsync.fulfilled, (state) => {
        state.status = 'approved';
      })
      .addCase(approveDateAsync.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.error.message;
      })
      .addCase(deleteNotificationAsync.pending, (state) => {
        state.status = 'loading';
      })
      .addCase(deleteNotificationAsync.fulfilled, (state, action) => {
        state.status = 'succeeded';
        state.notifications = state.notifications.filter(
          (notification) => notification.id !== action.payload.id
        );
      })
      .addCase(deleteNotificationAsync.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.error.message;
      });
  },
});
export const { clearNotifications } = notificationsSlice.actions;
export default notificationsSlice.reducer;
