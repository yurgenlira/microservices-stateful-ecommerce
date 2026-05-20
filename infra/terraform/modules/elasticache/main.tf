
resource "aws_elasticache_subnet_group" "this" {
  name       = "${var.project_name}-${var.environment}"
  subnet_ids = var.subnet_ids
}

resource "aws_elasticache_replication_group" "this" {
  replication_group_id = "${var.project_name}-${var.environment}"
  description          = "Redis cluster for ${var.environment}"

  engine         = "valkey"
  engine_version = "8.0"
  node_type      = var.node_type

  num_cache_clusters = var.num_cache_clusters

  subnet_group_name  = aws_elasticache_subnet_group.this.name
  security_group_ids = [var.security_group_id]

  transit_encryption_enabled = true
  auth_token                 = var.auth_token
  at_rest_encryption_enabled = true

  automatic_failover_enabled = var.num_cache_clusters > 1

  apply_immediately = var.environment == "dev"
}
