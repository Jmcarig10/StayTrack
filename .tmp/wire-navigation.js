const get=async id=>await figma.getNodeByIdAsync(id);
const link=async(from,toId)=>{const to=await get(toId);if(!from||!to)return false;await from.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:to.id,navigation:'NAVIGATE',transition:{type:'DISSOLVE',duration:0.2,easing:{type:'EASE_IN_AND_OUT'}}}]}]);return true;};
const all=(n,o=[])=>{o.push(n);if(n.children)for(const c of n.children)all(c,o);return o;};
const wireLabel=async(root,label,toId)=>{if(root.id===toId)return false;const n=all(root).find(x=>x.type==='TEXT'&&x.characters===label);return n?await link(n.parent,toId):false;};
const adminRoutes=['277:1161','277:1304','277:1415','278:1525','278:1636','278:1745','278:1825','278:1905','278:1985','278:2083','278:2163','278:2243','278:2323','278:2421','278:2501','278:2581','278:2661','278:2759','278:2839','278:2919','278:2999','278:3097','278:3177','278:3257','278:3337'];
const adminNav={Dashboard:'277:1041',Apartments:'277:1161',Tenants:'278:1636',Leases:'278:1985',Maintenance:'278:2323',Bills:'278:2661',Payments:'278:2999',Profile:'278:3337','Sign out':'278:3890'};
const tenantRoutes=['278:3681','278:3761']; const tenantNav={Dashboard:'283:1899',Maintenance:'278:3503',Profile:'278:3761','Sign out':'278:3890'};
let count=0;
for(const id of adminRoutes){const r=await get(id);if(!r)continue;for(const [label,to] of Object.entries(adminNav))if(await wireLabel(r,label,to))count++;}
for(const id of tenantRoutes){const r=await get(id);if(!r)continue;for(const [label,to] of Object.entries(tenantNav))if(await wireLabel(r,label,to))count++;}
return {count};
