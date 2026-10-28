const link = async (fromId, toId) => {
  const from = await figma.getNodeByIdAsync(fromId); const to = await figma.getNodeByIdAsync(toId);
  if (!from || !to) return false;
  await from.setReactionsAsync([{ trigger: { type: 'ON_CLICK' }, actions: [{ type: 'NODE', destinationId: to.id, navigation: 'NAVIGATE', transition: { type: 'DISSOLVE', duration: 0.2, easing: { type: 'EASE_IN_AND_OUT' } } }] }]);
  return true;
};
const routes = [
  ['277:1161',['277:1271','277:1282','277:1293'],['277:1415','278:1525','277:1304']],
  ['278:1636',['278:1724','278:1731','278:1738'],['278:1825','278:1905','278:1745']],
  ['278:1985',['278:2062','278:2069','278:2076'],['278:2163','278:2243','278:2083']],
  ['278:2323',['278:2400','278:2407','278:2414'],['278:2501','278:2581','278:2421']],
  ['278:2661',['278:2738','278:2745','278:2752'],['278:2839','278:2919','278:2759']],
  ['278:2999',['278:3076','278:3083','278:3090'],['278:3177','278:3257','278:3097']],
  ['278:3503',['278:3580','278:3587','278:3594'],['278:3681','278:3601','278:3681']]
];
let count = 0;
for (const [, rows, dests] of routes) for (let i=0;i<rows.length;i++) if (await link(rows[i], dests[i])) count++;
return { count };
