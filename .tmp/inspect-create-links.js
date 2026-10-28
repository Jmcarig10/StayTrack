
const ids=['272:349','275:913','309:198','309:256','309:258'];
const out=[];
for(const id of ids){const n=await figma.getNodeByIdAsync(id); out.push({id,name:n?.name,type:n?.type,parent:{id:n?.parent?.id,name:n?.parent?.name,type:n?.parent?.type},reactions:n?.reactions?.length||0});}
return out;