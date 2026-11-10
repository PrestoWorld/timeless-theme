// Mock for lodash
module.exports = {
  isEmpty: (val) => val == null || val === '',
  isNumber: (val) => typeof val === 'number',
  isString: (val) => typeof val === 'string',
  isBoolean: (val) => typeof val === 'boolean',
  isArray: Array.isArray,
  isObject: (val) => val !== null && typeof val === 'object',
  debounce: (fn) => fn,
  throttle: (fn) => fn,
  cloneDeep: (val) => JSON.parse(JSON.stringify(val)),
  merge: (...args) => Object.assign({}, ...args),
  get: (obj, path, defaultVal) => {
    const parts = path.split('.');
    let current = obj;
    for (const part of parts) {
      if (current == null || !(part in current)) return defaultVal;
      current = current[part];
    }
    return current;
  },
  set: (obj, path, val) => {
    const parts = path.split('.');
    let current = obj;
    for (let i = 0; i < parts.length - 1; i++) {
      if (!current[parts[i]]) current[parts[i]] = {};
      current = current[parts[i]];
    }
    current[parts[parts.length - 1]] = val;
    return obj;
  },
};
