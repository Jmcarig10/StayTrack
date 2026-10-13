const a=await figma.getNodeByIdAsync('284:5735');const b=await figma.getNodeByIdAsync('151:3230');
try{await a.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'NODE',destinationId:b.id,navigation:'NAVIGATE'}]}]);return JSON.stringify({ok:true,reactions:a.reactions.length});}catch(e){return JSON.stringify({ok:false,error:String(e)});}
