const ids=['166:607','166:720','170:898','170:992','170:1091','178:1448','219:2824','219:2825'];const out=[];
for(const id of ids){const n=await figma.getNodeByIdAsync(id);out.push({id,name:n?.name,type:n?.type,reactions:n?.reactions});}
return out;
