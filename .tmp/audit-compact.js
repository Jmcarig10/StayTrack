const ids=['275:746','275:799','275:860','275:913','278:3890','278:3944','278:3988','278:4013','278:4048','278:4070','277:1041','277:1161','277:1304','277:1415','278:1525','278:1636','278:1745','278:1825','278:1905','278:1985','278:2083','278:2163','278:2243','278:2323','278:2421','278:2501','278:2581','278:2661','278:2759','278:2839','278:2919','278:2999','278:3097','278:3177','278:3257','278:3337','283:1899','282:1262','282:1371','282:1486','282:1596','278:3503','278:3601','278:3681','278:3761','282:1698','282:1714','282:1723','282:1737','282:1755','283:1870','282:1764','282:1791','283:2424','283:2455'];
const auth=new Set(['275:746','275:799','275:860','275:913','278:3890','278:3944','278:3988','278:4013','278:4048','278:4070']);
const admin=new Set(['277:1041','277:1161','277:1304','277:1415','278:1525','278:1636','278:1745','278:1825','278:1905','278:1985','278:2083','278:2163','278:2243','278:2323','278:2421','278:2501','278:2581','278:2661','278:2759','278:2839','278:2919','278:2999','278:3097','278:3177','278:3257','278:3337']);
const tenant=new Set(['283:1899','282:1262','282:1371','282:1486','282:1596','278:3503','278:3601','278:3681','278:3761']);
const all=(n,o=[])=>{o.push(n);if(n.children)for(const c of n.children)all(c,o);return o;}; const present=new Set(ids); const details=[]; let total=0,invalid=[],zero=[]; const roles={auth:0,admin:0,tenant:0,other:0}; const roleViolations=[];
const group=id=>auth.has(id)?'auth':admin.has(id)?'admin':tenant.has(id)?'tenant':'other';
for(const id of ids){const root=await figma.getNodeByIdAsync(id);if(!root){details.push({id,missing:true});continue;}const links=all(root).flatMap(n=>(n.reactions||[]).flatMap(r=>(r.actions||[]).filter(a=>a.type==='NODE').map(a=>({from:n.id,name:n.name,to:a.destinationId||null}))));total+=links.length;if(!links.length)zero.push({id,name:root.name});for(const l of links){const to=l.to;if(!present.has(to))invalid.push({fromScreen:id,from:l.from,to});if(present.has(to))roles[group(to)]++;if((group(id)==='tenant'&&group(to)==='admin')||(group(id)==='admin'&&group(to)==='tenant'))roleViolations.push({fromScreen:id,from:l.from,to});}details.push({id,name:root.name,linkCount:links.length,links});}
const critical={
  flow2Landing:details.find(x=>x.id==='275:746')?.links||[],
  flow2Login:details.find(x=>x.id==='275:799')?.links||[],
  genericLogin:details.find(x=>x.id==='278:3890')?.links||[],
  authMissingCandidates:details.filter(x=>['278:3988','278:4013','278:4048','278:4070'].includes(x.id)).map(x=>({id:x.id,linkCount:x.linkCount||0})),
  tenantMaintenance:details.filter(x=>['278:3503','278:3601','278:3681','278:3761'].includes(x.id)).map(x=>({id:x.id,name:x.name,linkCount:x.linkCount||0}))
};
return {screenCount:details.filter(x=>!x.missing).length,interactionCount:total,invalidDestinationCount:invalid.length,invalidDestinations:invalid,zeroReactionScreens:zero,roleCounts:roles,roleViolations,critical};
