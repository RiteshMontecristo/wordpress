// Same cache-busting version as index.js — every relative import in this file and
// everything it loads must resolve to the identical URL as sales.js's own import of
// the same file, or that file gets loaded twice as two separate module instances.
const V = window.ajax_inventory?.asset_version ?? "";
const qs = V ? `?v=${V}` : "";

// Dynamic + Promise.all instead of static imports: static imports guarantee
// StoreSelector etc. are ready the instant this file's top-level code runs, which
// is what let .init() be called unconditionally right below. Dynamic imports don't
// make that guarantee — they resolve later — so everything that used to run right
// after the import block now has to wait for all seven to resolve first.
const [
  { StoreSelector },
  { CustomerSelector },
  { LayawaySelector },
  { ServiceSelector },
  { ProductSelector },
  { CartSelector },
  { CheckoutSelector },
] = await Promise.all([
  import(`./sales-module/store.js${qs}`),
  import(`./sales-module/customer.js${qs}`),
  import(`./sales-module/layaway.js${qs}`),
  import(`./sales-module/service.js${qs}`),
  import(`./sales-module/product.js${qs}`),
  import(`./sales-module/cart.js${qs}`),
  import(`./sales-module/checkout.js${qs}`),
]);

StoreSelector.init();
CustomerSelector.init();
LayawaySelector.init();
ServiceSelector.init();
ProductSelector.init();
CartSelector.init();
CheckoutSelector.init();

document.addEventListener("layaway:added", () => {
  CustomerSelector.updateLayaway();
});

document.addEventListener("displayCart", (e) => {
  CartSelector.displayCart();
});

document.addEventListener("call:calculateTotal", () => {
  CheckoutSelector.calculateTotal();
});
