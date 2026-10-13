const ids=['272:349','275:913','275:799','272:296','275:860'];
const out=[];
for(const id of ids){const n=await figma.getNodeByIdAsync(id);let p=n;const chain=[];while(p){chain.push({id:p.id,name:p.name,type:p.type});p=p.parent;}out.push({id,name:n?.name,type:n?.type,chain});}
return out;
