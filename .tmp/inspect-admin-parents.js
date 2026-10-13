const ids=['284:5601','284:5720','284:5867','284:5983','284:6086','284:6189','284:6327','284:6430','284:6533','284:6636','284:6751','284:6867','179:1511','151:3230','151:3338','151:3864','131:946','179:1731','275:913'];
const out=[];
for(const id of ids){const n=await figma.getNodeByIdAsync(id);const p=[];let x=n;for(let i=0;x&&i<4;i++,x=x.parent)p.push({id:x.id,name:x.name,type:x.type});out.push({id,chain:p});}
return JSON.stringify(out);
