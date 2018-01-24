import React, { Component } from 'react'
import { Link } from 'react-router-dom'
import { translate} from 'react-i18next'
import { func, object } from 'prop-types'

class MenuBarAdmin extends Component {

    static contextTypes = {
        t : func.isRequired
    }

    render() {
        const { t } = this.context
        const isSuperAdmin = (this.props.user.roles.find(role => role.includes("ROLE_SUPER_ADMIN")) !== undefined)
        return (
            <ul className="menu">
                <li><Link to={"/admin/utilisateurs"} className="button gray btn-user-conf"><span>{t('admin.user.name', {count: 2})}</span></Link></li>
                <li><Link to={"/admin/circuits-de-validation"} className="button gray btn-user-conf"><span>{t('admin.circuit.name', {count: 2})}</span></Link></li>
                <li><Link to={"/admin/groupes"} className="button gray btn-user-conf"><span>{t('admin.group.name', {count: 2})}</span></Link></li>
                <li><Link to={"/admin/types-classeur"} className="button gray btn-user-conf"><span>{t('admin.type.name', {count: 2})}</span></Link></li>
                <li><Link to={"/admin/collectivites"} className="button gray btn-user-conf"><span>{t('admin.collectivite.name', {count: 2})}</span></Link></li>
                {isSuperAdmin &&
                    <li><Link to={"/admin/documentations"} className="button gray btn-user-conf"><span>{t('common.help_board.title')}</span></Link></li>}
                {isSuperAdmin &&
                    <li><Link to={"/admin/emailing"} className="button gray btn-user-conf"><span>{t('admin.emailing.title')}</span></Link></li>}
                {isSuperAdmin &&
                    <li><Link to={"/admin/notes"} className="button gray btn-user-conf"><span>{t('admin.notes.title')}</span></Link></li>}
            </ul>
        )
    }
}

MenuBarAdmin.propTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(MenuBarAdmin)