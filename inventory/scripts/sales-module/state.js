/**
 * @typedef {Object} StoreState
 * @property {string|null} id
 * @property {string|null} name
 */

/**
 * @typedef {Object} CustomerState
 * @property {string|null} id
 * @property {string} firstName
 * @property {string} lastName
 * @property {string} address
 */

//  * @typedef {Object} CartItem
//  * @property {number} id
//  * @property {string} name
//  * @property {number} price
//  * @property {number} quantity

// @property {CartItems][]} cart
/**
 * @typedef {Object} AppState
 * @property {Array<Object>} cart
 * @property {CustomerState} customer
 * @property {number} layawayTotal
 * @property {number} creditTotal
 * @property {Array<Object>} services
 * @property {StoreState} location
 */

/** @type {AppState} */
export const AppState = {
  cart: [],
  customer: { id: null, firstName: "", lastName: "", address: "" },
  layawayTotal: 0,
  creditTotal: 0,
  services: [],
  location: { id: null, name: null },
};
