import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
// import getPropertiesSearch from 'services/propertySearchService';

export const fetchProperties = createAsyncThunk(
  'propertiesSearch/fetchProperties',
  async ({
    propertyType,
    city,
    listingType,
    bedrooms,
    bathrooms,
    minPrice,
    maxPrice,
  }) => {
    try {
      const response = await getPropertiesSearch({
        params: {
          property_type: propertyType || null,
          city: city || null,
          listing_type: listingType || null,
          num_of_bedrooms: bedrooms || null,
          num_of_bathrooms: bathrooms || null,
          min_price: minPrice || null,
          max_price: maxPrice || null,
        },
      });
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
    error: null,
  },
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchProperties.pending, (state) => {
        state.status = 'loading';
        state.error = null;
      })
      .addCase(fetchProperties.fulfilled, (state, action) => {
        state.status = 'succeeded';
        state.data = action.payload;
        state.error = null;
      })
      .addCase(fetchProperties.rejected, (state, action) => {
        state.status = 'failed';
        state.error = action.error.message;
      });
  },
});

export default propertiesSliceSearch.reducer;
