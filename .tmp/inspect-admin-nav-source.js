const root=await figma.getNodeByIdAsync('284:5720');
const all=(n,o=[])=>{o.push(n);if(n.children)for(const c of n.children)all(c,o);return o;};
const out=[];for(const label of ['Dashboard','Apartments','Tenants','Leases','Maintenance','Bills','Payments','Profile','Sign out']){const t=all(root).find(n=>n.type==='TEXT'&&n.characters===label);let p=t;const chain=[];for(let i=0;p&&i<5;i++,p=p.parent)chain.push({id:p.id,name:p.name,type:p.type,reactions:p.reactions?.length||0});out.push({label,text:t&&t.id,chain});}
return JSON.stringify(out);
