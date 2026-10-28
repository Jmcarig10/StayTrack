const get = async (id) => await figma.getNodeByIdAsync(id);
const link = async (from, toId) => {
  const to = await get(toId); if (!from || !to) return false;
  await from.setReactionsAsync([{ trigger:{type:'ON_CLICK'}, actions:[{type:'NODE',destinationId:to.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]); return true;
};
const text = (root, value) => root.findAll((n) => n.type === 'TEXT' && n.characters === value)[0] || null;
const routes = [
  {index:'277:1161',create:'277:1304',show:'277:1415',edit:'278:1525'},
  {index:'278:1636',create:'278:1745',show:'278:1825',edit:'278:1905'},
  {index:'278:1985',create:'278:2083',show:'278:2163',edit:'278:2243'},
  {index:'278:2323',create:'278:2421',show:'278:2501',edit:'278:2581'},
  {index:'278:2661',create:'278:2759',show:'278:2839',edit:'278:2919'},
  {index:'278:2999',create:'278:3097',show:'278:3177',edit:'278:3257'},
  {index:'278:3503',create:'278:3601',show:'278:3681'}
];
let count=0;
for (const r of routes) {
  const create=await get(r.create); const show=await get(r.show);
  if (await link(text(create,'Cancel'),r.index)) count++;
  if (await link(text(create,'Save changes'),r.show)) count++;
  if (await link(text(show,'Edit'),r.edit)) count++;
  if (r.edit) { const edit=await get(r.edit); if (await link(text(edit,'Cancel'),r.index)) count++; if (await link(text(edit,'Save changes'),r.show)) count++; }
}
return {count};
