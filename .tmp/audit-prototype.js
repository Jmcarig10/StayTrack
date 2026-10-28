const ids = [
  '275:746','275:799','275:860','275:913',
  '278:3890','278:3944','278:3988','278:4013','278:4048','278:4070',
  '277:1041','277:1161','277:1304','277:1415','278:1525',
  '278:1636','278:1745','278:1825','278:1905',
  '278:1985','278:2083','278:2163','278:2243',
  '278:2323','278:2421','278:2501','278:2581',
  '278:2661','278:2759','278:2839','278:2919',
  '278:2999','278:3097','278:3177','278:3257',
  '278:3337',
  '283:1899','282:1262','282:1371','282:1486','282:1596','278:3503','278:3601','278:3681','278:3761',
  '282:1698','282:1714','282:1723','282:1737','282:1755','283:1870',
  '282:1764','282:1791','283:2424','283:2455'
];
const all = (n, out=[]) => { out.push(n); if (n.children) for (const c of n.children) all(c,out); return out; };
const out=[]; let screenCount=0, interactionCount=0;
for (const id of ids) {
  const n=await figma.getNodeByIdAsync(id); if(!n){out.push({id,missing:true});continue;}
  screenCount++;
  const links=all(n).filter(x=>x.reactions&&x.reactions.length).flatMap(x=>x.reactions.map(r=>({nodeId:x.id,nodeName:x.name,trigger:r.trigger?.type||null,actions:(r.actions||[]).map(a=>({type:a.type,destinationId:a.destinationId||null,overlay:a.overlayRelativePosition||null}))})));
  interactionCount+=links.length;
  out.push({id,name:n.name,links});
}
return {screenCount,interactionCount,screens:out};
