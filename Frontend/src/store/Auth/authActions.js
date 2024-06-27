import { createAsyncThunk } from '@reduxjs/toolkit';
import {
  registerApi,
  loginApi,
  forgetPasswordApi,
  resetPasswordApi,
} from 'services/authService';

export const register = createAsyncThunk(
  'auth/register',
  async (data, thunkAPI) => {
    try {
      const response = await registerApi(data);
      return response.data;
    } catch (error) {
      return thunkAPI.rejectWithValue(error.response.data);
    }
  }
);

export const login = createAsyncThunk('auth/login', async (data, thunkAPI) => {
  try {
    const response = await loginApi(data);

    // store user's token and data in local storage
    localStorage.setItem('token', response.data.access_token);
    localStorage.setItem('user', JSON.stringify(response.data.user));
    return response.data;
  } catch (error) {
    return thunkAPI.rejectWithValue(error.response.data);
  }
});

export const forgetPassword = createAsyncThunk(
  'auth/forget-password',
  async (data, thunkAPI) => {
    try {
      const response = await forgetPasswordApi(data);
      return response.data;
    } catch (error) {
      return thunkAPI.rejectWithValue(error.response.data);
    }
  }
);

export const resetPassword = createAsyncThunk(
  'auth/reset-password',
  async (data, thunkAPI) => {
    try {
      const response = await resetPasswordApi(data);
      return response.data;
    } catch (error) {
      return thunkAPI.rejectWithValue(error.response.data);
    }
  }
);
