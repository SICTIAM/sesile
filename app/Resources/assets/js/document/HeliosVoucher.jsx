import React, { Component } from 'react'
import {func, object} from 'prop-types'
import { translate } from 'react-i18next'
import Moment from 'moment'

class HeliosVoucher extends Component {

    static contextTypes = { t: func }

    render () {
        const { t } = this.context
        const { voucher } = this.props

        return (
            <div className="cell medium-12" style={{marginBottom: '20px'}}>
                <div className="grid-x">
                    <div className="cell medium-12">
                        <h3>
                            {t('common.helios.voucher_infos')}
                        </h3>
                    </div>
                </div>
                <div className="grid-x" style={{marginBottom: '10px'}}>
                    <div className="cell medium-3 text-bold">{ t('common.helios.number') }</div>
                    <div className="cell medium-3">{ voucher.id }</div>
                    <div className="cell medium-3 text-bold">{ t('common.helios.fiscal_year') }</div>
                    <div className="cell medium-3">{ voucher.exercice }</div>
                </div>
                <div className="grid-x" style={{marginBottom: '10px'}}>
                    <div className="cell medium-3 text-bold">{ t('common.helios.type') }</div>
                    <div className="cell medium-3">{ voucher.type }</div>
                    <div className="cell medium-3 text-bold">{ t('common.helios.year_to_date') }</div>
                    <div className="cell medium-3">{ voucher.mt_cumul_annuel }</div>
                </div>
                <div className="grid-x" style={{marginBottom: '10px'}}>
                    <div className="cell medium-3 text-bold">{ t('common.helios.issue_date') }</div>
                    <div className="cell medium-3">{ Moment(voucher.date_em).format('LL') }</div>
                    <div className="cell medium-3"></div>
                    <div className="cell medium-3"></div>
                </div>
                <div className="grid-x" style={{marginBottom: '10px'}}>
                    <div className="cell medium-3 text-bold">{ t('common.helios.attachments_count') }</div>
                    <div className="cell medium-3">{ voucher.nb_piece }</div>
                    <div className="cell medium-3"></div>
                    <div className="cell medium-3"></div>
                </div>
                <div className="grid-x">
                    <div className="cell medium-3 text-bold">{ t('common.helios.amount') }</div>
                    <div className="cell medium-3">{ voucher.mt_bord_h_t }</div>
                    <div className="cell medium-3"></div>
                    <div className="cell medium-3"></div>
                </div>
            </div>
        )
    }
}

HeliosVoucher.propsType = {
    voucher: object.isRequired
}

export default translate(['sesile'])(HeliosVoucher)