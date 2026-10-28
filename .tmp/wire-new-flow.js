const link = async (fromId, toId) => {
  const from = await figma.getNodeByIdAsync(fromId);
  const to = await figma.getNodeByIdAsync(toId);
  if (!from || !to) throw new Error(`Missing prototype node: ${fromId} -> ${toId}`);
  const reactions = [{
    trigger: { type: 'ON_CLICK' },
    actions: [{
      type: 'NODE',
      destinationId: to.id,
      navigation: 'NAVIGATE',
      transition: { type: 'DISSOLVE', duration: 0.2, easing: { type: 'EASE_IN_AND_OUT' } }
    }]
  }];
  await from.setReactionsAsync(reactions);
  return `${from.name} -> ${to.name}`;
};
const links = await Promise.all([
  link('278:3877', '278:3890'), // Landing sign-in -> Login
  link('278:3884', '278:3944'), // Landing register -> Register
  link('278:3938', '277:1041'), // Login submit -> Admin dashboard
  link('278:3943', '278:3944'), // Login register link -> Register
  link('278:3982', '278:3420'), // Register submit -> Tenant dashboard branch
  link('278:3987', '278:3890'), // Register sign-in -> Login
  link('278:3462', '278:3890'), // Tenant sign-out -> Login
  link('277:1111', '278:3890'), // Admin sign-out -> Login
  link('277:1063', '277:1161'), // Admin Apartments nav
  link('277:1070', '278:1636'), // Admin Tenants nav
  link('277:1076', '278:1985'), // Admin Leases nav
  link('277:1082', '278:2323'), // Admin Maintenance nav
  link('277:1086', '278:2661'), // Admin Bills nav
  link('277:1092', '278:2999'), // Admin Payments nav
  link('277:1098', '278:3337'), // Admin Profile nav
  link('278:3443', '278:3503'), // Tenant Maintenance nav
  link('278:3452', '278:3761') // Tenant Profile nav
]);
return links;
