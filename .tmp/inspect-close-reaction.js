const out=[];for(const id of ['170:1360','170:1294']){const n=await figma.getNodeByIdAsync(id);out.push({id,reactions:n&&n.reactions});}return out;
