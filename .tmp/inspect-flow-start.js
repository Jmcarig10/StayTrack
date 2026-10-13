const n=await figma.getNodeByIdAsync('275:746'); return {fsp:n.flowStartingPoint,prototypeStart:n.prototypeStartNodeId,reactions:n.reactions?.length||0};
