const ids=['166:553','166:666','166:755','170:844','170:938','170:1368','219:2749','219:2900','170:1037','170:1291'];
const all=(n,o=[])=>{o.push(n);if(n.children)for(const c of n.children)all(c,o);return o};
const out=[];let total=0;
for(const id of ids){const root=await figma.getNodeByIdAsync(id);const links=all(root).flatMap(n=>(n.reactions||[]).flatMap(r=>(r.actions||[]).map(a=>({from:n.id,name:n.name,type:a.type,to:a.destinationId||null,nav:a.navigation||null}))));total+=links.length;out.push({id,name:root.name,linkCount:links.length,links});}
return {screenCount:ids.length,totalActions:total,overlays:out.flatMap(x=>x.links).filter(l=>l.nav==='OVERLAY').length,logoutTargets:out.flatMap(x=>x.links).filter(l=>l.to==='275:799').length,details:out};
