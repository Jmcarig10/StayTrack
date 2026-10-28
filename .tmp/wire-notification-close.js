const n=await figma.getNodeByIdAsync('283:1875');await n.setReactionsAsync([{trigger:{type:'ON_CLICK'},actions:[{type:'CLOSE'}]}]);return {id:n.id,reactions:n.reactions};
