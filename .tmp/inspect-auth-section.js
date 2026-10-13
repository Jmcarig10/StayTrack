const s=await figma.getNodeByIdAsync('205:639');
return {section:{x:s.x,y:s.y,w:s.width,h:s.height},children:s.children.map(n=>({id:n.id,name:n.name,type:n.type,x:n.x,y:n.y,w:n.width,h:n.height}))};
