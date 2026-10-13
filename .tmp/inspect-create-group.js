const n=await figma.getNodeByIdAsync('309:379');
return {node:n?{id:n.id,name:n.name,type:n.type,x:n.x,y:n.y,w:n.width,h:n.height,visible:n.visible}:null,children:n?.children?.map(c=>({id:c.id,name:c.name,type:c.type,x:c.x,y:c.y,w:c.width,h:c.height,visible:c.visible}))};
