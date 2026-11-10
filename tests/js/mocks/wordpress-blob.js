// Mock for @wordpress/blob
module.exports = {
  isBlobURL: (url) => typeof url === 'string' && url.startsWith('blob:'),
  createBlobURL: () => 'blob:mock',
  revokeBlobURL: () => {},
};
