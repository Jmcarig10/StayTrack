const n=await figma.getNodeByIdAsync('309:198');
return n.children.map(c=>({id:c.id,name:c.name,type:c.type,x:c.x,y:c.y,w:c.width,h:c.height,visible:c.visible}));
