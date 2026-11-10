// Mock for react-dom/client
module.exports = {
  createRoot: () => ({
    render: jest.fn(),
    unmount: jest.fn(),
  }),
  hydrateRoot: () => ({
    render: jest.fn(),
    unmount: jest.fn(),
  }),
};
