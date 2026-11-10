module.exports = {
  rootDir: '.',
  testEnvironment: 'jsdom',
  testMatch: ['<rootDir>/tests/js/__tests__/**/*.test.js'],
  transform: {
    '^.+\\.(js|jsx|ts|tsx)$': ['babel-jest', { configFile: './babel.config.js' }],
  },
  moduleFileExtensions: ['ts', 'tsx', 'js', 'jsx', 'json'],
  moduleNameMapper: {
    '^@wordpress/(.*)$': '<rootDir>/tests/js/mocks/wordpress-$1.js',
    '^react$': '<rootDir>/tests/js/mocks/react.js',
    'react-dom/client': '<rootDir>/tests/js/mocks/react-dom-client.js',
    '^classnames$': '<rootDir>/tests/js/mocks/classnames.js',
    '^lodash$': '<rootDir>/tests/js/mocks/lodash.js',
    '\\.scss$': '<rootDir>/tests/js/mocks/style-mock.js',
    '\\.css$': '<rootDir>/tests/js/mocks/style-mock.js',
    'jankx-inspector/(JankxInspector|ResponsiveControl)': '<rootDir>/tests/js/mocks/jankx-inspector.js',
    '^react/jsx-runtime$': '<rootDir>/tests/js/mocks/react-jsx-runtime.js',
    '^react/jsx-dev-runtime$': '<rootDir>/tests/js/mocks/react-jsx-runtime.js',
    '^html-react-parser$': '<rootDir>/tests/js/mocks/html-react-parser.js',
  },  setupFiles: ['<rootDir>/tests/js/setup.js'],
  collectCoverageFrom: [
    'resources/blocks/**/*.{ts,tsx,js,jsx}',
    '!resources/blocks/**/dist/**',
    '!resources/blocks/**/node_modules/**',
  ],
  coverageDirectory: 'tests/js/coverage',
  verbose: true,
};
