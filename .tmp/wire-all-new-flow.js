const login = '278:3890';
const admin = {
  dashboard: '277:1041', apartments: { index: '277:1161', create: '277:1304', show: '277:1415', edit: '278:1525' },
  tenants: { index: '278:1636', create: '278:1745', show: '278:1825', edit: '278:1905' },
  leases: { index: '278:1985', create: '278:2083', show: '278:2163', edit: '278:2243' },
  maintenance: { index: '278:2323', create: '278:2421', show: '278:2501', edit: '278:2581' },
  bills: { index: '278:2661', create: '278:2759', show: '278:2839', edit: '278:2919' },
  payments: { index: '278:2999', create: '278:3097', show: '278:3177', edit: '278:3257' },
  profile: '278:3337'
};
const tenant = {
  dashboard: '278:3420', maintenance: { index: '278:3503', create: '278:3601', show: '278:3681' }, profile: '278:3761'
};

const all = [admin.dashboard, admin.apartments.index, admin.apartments.create, admin.apartments.show, admin.apartments.edit,
  admin.tenants.index, admin.tenants.create, admin.tenants.show, admin.tenants.edit,
  admin.leases.index, admin.leases.create, admin.leases.show, admin.leases.edit,
  admin.maintenance.index, admin.maintenance.create, admin.maintenance.show, admin.maintenance.edit,
  admin.bills.index, admin.bills.create, admin.bills.show, admin.bills.edit,
  admin.payments.index, admin.payments.create, admin.payments.show, admin.payments.edit,
  admin.profile, tenant.dashboard, tenant.maintenance.index, tenant.maintenance.create, tenant.maintenance.show, tenant.profile];

const node = async (id) => await figma.getNodeByIdAsync(id);
const walk = (root, fn) => {
  if (!root || !root.children) return;
  for (const child of root.children) { fn(child, root); walk(child, fn); }
};
const containerWithText = (root, text) => {
  let found = null;
  walk(root, (child, parent) => {
    if (!found && child.type === 'TEXT' && (child.characters || '').trim() === text) found = parent;
  });
  return found;
};
const named = (root, name) => {
  let found = null;
  walk(root, (child) => { if (!found && child.name === name) found = child; });
  return found;
};
const setLink = async (from, to) => {
  if (!from || !to) return false;
  await from.setReactionsAsync([{ trigger: { type: 'ON_CLICK' }, actions: [{ type: 'NODE', destinationId: to.id, navigation: 'NAVIGATE', transition: { type: 'DISSOLVE', duration: 0.2, easing: { type: 'EASE_IN_AND_OUT' } } }] }]);
  return true;
};
const connectSidebar = async (frameId, map) => {
  const frame = await node(frameId); let count = 0;
  for (const [label, targetId] of Object.entries(map)) {
    if (await setLink(containerWithText(frame, label), await node(targetId))) count++;
  }
  if (await setLink(containerWithText(frame, 'Sign out'), await node(login))) count++;
  return count;
};

const adminNav = { Dashboard: admin.dashboard, Apartments: admin.apartments.index, Tenants: admin.tenants.index, Leases: admin.leases.index, Maintenance: admin.maintenance.index, Bills: admin.bills.index, Payments: admin.payments.index, Profile: admin.profile };
const tenantNav = { Dashboard: tenant.dashboard, Maintenance: tenant.maintenance.index, Profile: tenant.profile };
let navLinks = 0;
for (const id of all.slice(0, 26)) navLinks += await connectSidebar(id, adminNav);
for (const id of all.slice(26)) navLinks += await connectSidebar(id, tenantNav);

const crud = [admin.apartments, admin.tenants, admin.leases, admin.maintenance, admin.bills, admin.payments, tenant.maintenance];
let crudLinks = 0;
for (const route of crud) {
  const indexFrame = await node(route.index);
  const table = named(indexFrame, 'Table');
  const rows = table && table.children ? table.children.filter((c) => c.height === 62) : [];
  if (rows[0] && await setLink(rows[0], await node(route.show))) crudLinks++;
  if (rows[1] && route.edit && await setLink(rows[1], await node(route.edit))) crudLinks++;
  if (rows[2] && await setLink(rows[2], await node(route.show))) crudLinks++;
  const showFrame = await node(route.show);
  if (route.edit && await setLink(containerWithText(showFrame, 'Edit'), await node(route.edit))) crudLinks++;
  const createFrame = await node(route.create);
  if (await setLink(containerWithText(createFrame, 'Cancel'), await node(route.index))) crudLinks++;
  if (await setLink(containerWithText(createFrame, 'Save changes'), await node(route.show))) crudLinks++;
  if (route.edit) {
    const editFrame = await node(route.edit);
    if (await setLink(containerWithText(editFrame, 'Cancel'), await node(route.index))) crudLinks++;
    if (await setLink(containerWithText(editFrame, 'Save changes'), await node(route.show))) crudLinks++;
  }
}
return { navLinks, crudLinks };
