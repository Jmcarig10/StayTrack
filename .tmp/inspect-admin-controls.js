const ids=['284:5601','284:5720','284:5867','284:5983','284:6086','284:6189','284:6327','284:6430','284:6533','284:6636','284:6751','284:6867'];
const wanted=/^(Dashboard|Apartments|Tenants|Leases|Maintenance|Bills|Payments|Profile|Sign out|Add apartment|Save apartment|Create active lease|Create lease|Cancel|Save changes|Edit apartment|Edit tenant|Edit lease|Back to apartments|Back to tenants|Back to leases|View details|View|Edit|Delete|＋ Add apartment|＋ Add tenant)$/;
const out=[];
for(const id of ids){const root=await figma.getNodeByIdAsync(id);if(!root)continue;out.push({id,name:root.name,controls:root.findAll(n=>n.type==='TEXT'&&wanted.test(n.characters)).map(n=>({id:n.id,text:n.characters,parent:n.parent?.id,parentName:n.parent?.name,pp:n.parent?.parent?.id,ppName:n.parent?.parent?.name,x:n.x,y:n.y}))});}
return out;
