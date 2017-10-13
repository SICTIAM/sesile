import React, { Component } from 'react'
import { Link } from 'react-router-dom'
import { translate} from 'react-i18next'
import Proptyes from 'prop-types'

class MenuBarAdmin extends Component {

    static contextTypes = {
        t : Proptyes.func.isRequired
    }

    render() {
        const { t } = this.context
        return (
            <ul className="menu">
                <li><Link to={"/admin/utilisateurs"} className="button gray btn-user-conf"><span>{t('admin.user.name', {count: 2})}</span></Link></li>
                <li><Link to={"/admin/circuits-de-validation"} className="button gray btn-user-conf"><span>{t('admin.circuit.name', {count: 2})}</span></Link></li>
                <li><Link to={"/admin/groupes"} className="button gray btn-user-conf"><span>{t('admin.group.name', {count: 2})}</span></Link></li>
                <li><Link to={"/admin/types-classeur"} className="button gray btn-user-conf"><span>{t('admin.type.name', {count: 2})}</span></Link></li>
                <li><Link to={"/admin/collectivites"} className="button gray btn-user-conf"><span>{t('admin.collectivite.name', {count: 2})}</span></Link></li>
            </ul>
        )
    }
}

export default translate(['sesile'])(MenuBarAdmin)