import React from 'react';
import Tabl from './TableComponent';
import Column from './ColumnComponent';

const data = [{
	firstname: 'John',
	lastname: 'Doe',
	email: 'john@example.com'
}, {
	firstname: 'Mary',
	lastname: 'Moe',
	email: 'mary@example.com'
}, {
	firstname: 'July',
	lastname: 'Dooley',
	email: 'july@example.com'
}];


class TableContainer extends React.Component {
	render() {
		return (
			<div>
				<Tabl data={data}>
					<Column name="firstname" value="Firstname"/>
					<Column name="lastname" value="Lastname"/>
					<Column name="email" value="Email"/>
				</Tabl>
			</div>
		);
	}
}

export default TableContainer;