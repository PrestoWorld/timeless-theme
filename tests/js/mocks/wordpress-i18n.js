// Mock for @wordpress/i18n
module.exports = {
  __: (text) => text,
  _e: (text) => console.log(text),
  _x: (text) => text,
  _n: (singular, plural, count) => (count === 1 ? singular : plural),
};
