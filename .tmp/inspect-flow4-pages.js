const ids=['166:553','219:2749','272:349','170:1357'];
const out=[];
for(const id of ids){let n=await figma.getNodeByIdAsync(id);const chain=[];let p=n;while(p){chain.push({id:p.id,name:p.name,type:p.type});p=p.parent;}out.push({id,chain});}
return out;
