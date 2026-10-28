const ids=['166:600','166:602','166:607','219:2815','219:2814','219:2825'];const out=[];
for(const id of ids){const n=await figma.getNodeByIdAsync(id);out.push({id,name:n?.name,type:n?.type,locked:n?.locked,visible:n?.visible,opacity:n?.opacity,parent:n?.parent&&{id:n.parent.id,name:n.parent.name,type:n.parent.type},main:n?.type==='INSTANCE'&&n.mainComponent?n.mainComponent.id:null});}
return out;
