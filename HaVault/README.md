```shell
docker-compose up -d
docker exec -it vault1 vault operator init
# After running the initialization command, Vault will output:
# 1- Unseal Keys (multiple keys)
# 2- Root Token
# Step 2: Unseal vault1
docker exec -it vault1 vault operator unseal <UNSEAL_KEY_1>

# step 3: Use the following command to have vault2 join the cluster
docker exec -it vault2 vault operator raft join http://vault1:8202
# Step 4: Unseal vault2
Since vault2 is now part of the cluster, it shares the same unseal keys as vault1. Follow the same steps to unseal vault2:
docker exec -it vault2 vault operator unseal <UNSEAL_KEY_1>

# step 5: Use the following command to have vault3 join the cluster
docker exec -it vault3 vault operator raft join http://vault1:8202
# Step 6: Unseal vault3
Since vault3 is now part of the cluster, it shares the same unseal keys as vault1. Follow the same steps to unseal vault3:
docker exec -it vault3 vault operator unseal <UNSEAL_KEY_1>
# Step 7: Verify the Cluster
vault operator raft list-peers 
vault status



docker exec -it vault1 vault operator init -key-shares=1 -key-threshold=1 # note for demo purpose -key-shares=1 -key-threshold=1
Unseal Key 1: cZimP05LWtmLhSWoN93y5zJean6BeO6BMJAdJuR7N0s=

Initial Root Token: hvs.on4CJhYK0PzNs0kdaEdXTpEd

docker exec -it vault1 vault operator unseal cZimP05LWtmLhSWoN93y5zJean6BeO6BMJAdJuR7N0s=

docker exec -it vault2 vault operator raft join http://vault1:8202

docker exec -it vault2 vault operator unseal cZimP05LWtmLhSWoN93y5zJean6BeO6BMJAdJuR7N0s=

docker exec -it vault3 vault operator raft join http://vault1:8202
docker exec -it vault3 vault operator unseal cZimP05LWtmLhSWoN93y5zJean6BeO6BMJAdJuR7N0s=

export VAULT_TOKEN=hvs.on4CJhYK0PzNs0kdaEdXTpEd

#remove node from cluster
vault operator raft remove-peer vault3 
# list nodes in the cluster
vault operator raft list-peers
```
