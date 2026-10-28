const n=await figma.getNodeByIdAsync('219:2825');await n.setReactionsAsync([]);return {cleared:n.id,reactions:n.reactions.length};
