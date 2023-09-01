<?php

namespace Oh86\JYH;

class Constant
{
    const UserGroupDevAdmin = 0;
    const UserGroupSuperAdmin = 1;
    const UserGroupServiceAreaAdmin = 2;
    const UserGroupServiceAreaUser = 3;
    const UserGroupAuditor = 9;
    const UserGroupUser = 10;

    const SyncOpDelete = 0;
    const SyncOpCreate = 1;
    const SyncOpUpdate = 2;
}