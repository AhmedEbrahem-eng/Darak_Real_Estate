import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import axios from 'axios';

export const fetchProperties = createAsyncThunk(
  'properties/fetchProperties',
  async ({ propertyType, locationId }) => {
    try {
      const response = await axios.get(
        'http://127.0.0.1:8000/api/properties/search/filter',
        {
          params: {
            property_type: propertyType,
            location_id: locationId,
          },
        }
      );
      return response.data;
    } catch (error) {
      throw Error('Failed to fetch properties');
    }
  }
);

const propertiesSliceSearch = createSlice({
  name: 'propertiesSearch',
  initialState: {
    data: [],
    status: 'idle',
    error: null, // Add an error field to store fetch errors
  },
  reducers: {}, // Add reducers if needed
  extraReducers: (builder) => {
    builder
      .addCase(fetchProperties.pending, (state) => {
        state.status = 'loading';
        state.error = null; // Reset error state on pending
      })
      .addCase(fetchProperties.fulfilled, (state, action) => {
        state.status = 'succeeded';
        state.data = action.payload;
        state.error = null; // Reset error state on success
      })
      .addCase(fetchProperties.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.error.message; // Store the error message
      });
  },
});

export default propertiesSliceSearch.reducer;
